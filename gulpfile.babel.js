import gulp from 'gulp'
import merge from 'merge-stream'
import notifier from 'node-notifier'
import postcssImportUrl from 'postcss-import-url'
import postcssFlexbugsFixes from 'postcss-flexbugs-fixes'
import rucksack from 'rucksack-css'
import lost from 'lost'
import prefixer from 'autoprefixer'
import cssnano from 'cssnano'
import gp from 'gulp-load-plugins'
const $ = gp()
import minimist from 'minimist'
import config from './config.json'

let options = minimist(process.argv.slice(2), config.knownOptions)

let stylesTasks = []
let scriptsTasks = []
let copyTasks = []

const processors = [
  postcssImportUrl({ modernBrowser: true }),
  postcssFlexbugsFixes(),
  rucksack({ inputPseudo: false, quantityQueries: false }),
  lost(),
  prefixer(),
  cssnano({ safe: true }),
]

// Error handler
const onError = function (error) {
  notifier.notify({
    title: 'Error',
    message: 'Compilation failure.',
  })

  console.log(error)
  this.emit('end')
}

// Copy files
for (let task in config.copy) {
  copyTasks.push('copy.' + task)

  gulp.task('copy.' + task, done => {
    return gulp.src(config.copy[task].src)
    .pipe($.plumber({ errorHandler: onError }))
    .pipe($.if(!options.skipcache, $.newer(config.copy[task].dst + (config.copy[task].name || ''))))
    .pipe($.if(typeof config.copy[task].src === 'object', $.concat(config.copy[task].name || 'dummy')))
    .pipe($.if(Boolean(config.copy[task].header), $.header(config.copy[task].header)))
    .pipe($.if(Boolean(config.copy[task].replace), $.each((content, file, callback) => {
      let newContent = content
      for (let key in config.copy[task].replace) {
        newContent = newContent.replace(new RegExp(key, 'gm'), config.copy[task].replace[key])
      }
      callback(null, newContent)
    })))
    .pipe($.if(Boolean(config.copy[task].footer), $.footer(config.copy[task].footer)))
    .pipe($.if(Boolean(config.copy[task].name), $.rename(config.copy[task].name)))
    .pipe(gulp.dest(config.copy[task].dst))
    .pipe($.notify({ 'title': 'Copy completed!', 'message': 'copy: ' + task, sound: true, onLast: true }))

    done()
  })
};

// Compile sass
for (let task in config.styles) {
  stylesTasks.push('styles.' + task)

  gulp.task('styles.' + task, done => {
    return gulp.src(config.styles[task].src)
    .pipe($.plumber({ errorHandler: onError }))
    .pipe($.if(!options.skipcache, $.newer({ dest: (config.styles[task].dst + config.styles[task].name), extra: config.styles[task].watch })))
    .pipe($.if(typeof config.styles[task].src === 'object', $.concat(config.styles[task].name)))
    .pipe($.if(!options.production, $.sourcemaps.init()))
    .pipe($.sass())
    .pipe($.postcss(processors))
    .pipe($.rename(config.styles[task].name))
    .pipe($.if(!options.production, $.sourcemaps.write('./')))
    .pipe($.size({
      title: 'styles.' + task,
      gzip: true,
    }))
    .pipe(gulp.dest(config.styles[task].dst))
    .pipe($.notify({ 'title': 'Sass compiled!', 'message': '<%= file.relative %>', sound: true, onLast: true }))

    done()
  })
};

// Compile js
for (let task in config.scripts) {
  scriptsTasks.push('scripts.' + task)
  gulp.task('scripts.' + task, done => {
    return gulp.src(config.scripts[task].src)
    .pipe($.plumber({ errorHandler: onError }))
    .pipe($.if(!options.skipcache, $.newer(config.scripts[task].dst + config.scripts[task].name)))
    .pipe($.if(!options.production, $.sourcemaps.init()))
    .pipe($.if(typeof config.scripts[task].src === 'object', $.concat(config.scripts[task].name)))
    .pipe($.if(Boolean(config.scripts[task].babel), $.babel({ compact: false })))
    .pipe($.include()) // include & require files after babel, ie only babelify my own files, not included vendor files
    .pipe($.if(options.production, $.uglify()))
    .pipe($.rename(config.scripts[task].name))
    .pipe($.if(!options.production, $.sourcemaps.write('./')))
    .pipe($.size({
      title: 'scripts.' + task,
      gzip: true,
    }))
    .pipe(gulp.dest(config.scripts[task].dst))
    .pipe($.notify({ 'title': 'Javascript compiled!', 'message': '<%= file.relative %>', sound: true, onLast: true }))

    done()
  })
}

// Optimize images
gulp.task('images', () => {
  let tasks = []

  for (let task in config.images) {
    let t = gulp.src(config.images[task].src)
    .pipe($.plumber({ errorHandler: onError }))
    .pipe($.imagemin())
    .pipe(gulp.dest(config.images[task].dst))
    .pipe($.size({title: 'images'}))
    .pipe($.notify({ 'title': 'Images processed!', 'message': 'Done!', sound: true, onLast: true }))

    tasks.push(t)
  };

  return merge(tasks)
})

gulp.task('copy', gulp.series(...copyTasks, done => {
    notifier.notify({ title: 'Copy Files', message: 'Done!' })
    done()
}))
gulp.task('styles', gulp.series(...stylesTasks, done => {
    notifier.notify({ title: 'Compile Styles', message: 'Done!' })
    done()
}))
gulp.task('scripts', gulp.series(...scriptsTasks, done => {
    notifier.notify({ title: 'Compile Scripts', message: 'Done!' })
    done()
}))
gulp.task('default', gulp.series('copy', gulp.parallel('styles', 'scripts')), () => notifier.notify({ title: (options.production ? 'Production' : 'Development') + ' Build', message: 'Done!' })) // [...copyTasks, ...stylesTasks, ...scriptsTasks]

gulp.task('watch', () => {
  for (let task in config.styles) {
    if (config.styles[task].watch) {
      gulp.watch(config.styles[task].watch, gulp.series('styles.' + task))
    }
  }

  for (let task in config.scripts) {
    if (config.scripts[task].watch) {
      gulp.watch(config.scripts[task].watch, gulp.series('scripts.' + task))
    }
  }
})
