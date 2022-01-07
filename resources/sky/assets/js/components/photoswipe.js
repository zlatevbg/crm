/* global unikatSettings, PhotoSwipe, PhotoSwipeUI_Default */
const photoswipe = (() => {
  const template = '<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true"><div class="pswp__bg"></div><div class="pswp__scroll-wrap"><div class="pswp__container"><div class="pswp__item"></div><div class="pswp__item"></div><div class="pswp__item"></div></div><div class="pswp__ui pswp__ui--hidden"><div class="pswp__top-bar"><div class="pswp__counter"></div><button class="pswp__button pswp__button--close" title="' + unikatSettings.photoswipe.close + '"></button><button class="pswp__button pswp__button--share" title="' + unikatSettings.photoswipe.share + '"></button><button class="pswp__button pswp__button--fs" title="' + unikatSettings.photoswipe.fullscreen + '"></button><button class="pswp__button pswp__button--zoom" title="' + unikatSettings.photoswipe.zoom + '"></button><div class="pswp__preloader"><div class="pswp__preloader__icn"><div class="pswp__preloader__cut"><div class="pswp__preloader__donut"></div></div></div></div></div><div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap"><div class="pswp__share-tooltip"></div></div><button class="pswp__button pswp__button--arrow--left" title="' + unikatSettings.photoswipe.prev + '"></button><button class="pswp__button pswp__button--arrow--right" title="' + unikatSettings.photoswipe.next + '"></button><div class="pswp__caption"><div class="pswp__caption__center"></div></div></div></div></div>'
  document.body.insertAdjacentHTML('beforeend', template)

  const parseThumbnails = (gallery) => {
    const slides = []
    for (const link of gallery.querySelectorAll('.photoswipe')) {
      const size = link.getAttribute('data-size').split('x')
      const slide = {
        src: link.href,
        w: parseInt(size[0], 10),
        h: parseInt(size[1], 10),
        pid: link.getAttribute('data-id'),
      }

      if (link.title) {
        slide.title = link.title
      }

      if (link.children.length > 0) {
        const img = link.children[0]
        slide.msrc = img.src
        slide.thumbnail = img
      }

      slides.push(slide)
    }

    return slides
  }

  const parseHash = () => {
    const hash = window.location.hash.substring(1)
    const params = {}

    if (hash.length < 5) { // #&pid=1&gid=2
      return params
    }

    const vars = hash.split('&')
    for (let i = 0; i < vars.length; i++) {
      if (!vars[i]) {
        continue
      }

      const pair = vars[i].split('=')
      if (pair.length < 2) {
        continue
      }

      params[pair[0]] = pair[1]
    }

    if (params.gid) {
      params.gid = parseInt(params.gid, 10)
    }

    return params
  }

  const open = (index, gallery, disableAnimation, fromURL) => {
    const slides = parseThumbnails(gallery)
    const options = {
      galleryPIDs: true,
      galleryUID: gallery.getAttribute('data-pswp-uid'),
      getThumbBoundsFn: (index) => {
        if (slides[index].thumbnail) {
          const thumbnail = slides[index].thumbnail
          const rect = thumbnail.getBoundingClientRect()

          return {
            x: rect.left,
            y: rect.top + window.pageYOffset,
            w: rect.width,
          }
        }
      },
    }

    if (fromURL) {
      if (options.galleryPIDs) {
        for (let j = 0; j < slides.length; j++) {
          if (slides[j].pid === index) {
            options.index = j
            break
          }
        }
      } else {
        options.index = parseInt(index, 10) - 1
      }
    } else {
      options.index = parseInt(index, 10)
    }

    if (isNaN(options.index)) {
      return
    }

    if (disableAnimation) {
      options.showAnimationDuration = 0
    }

    gallery = new PhotoSwipe(document.querySelectorAll('.pswp')[0], PhotoSwipeUI_Default, slides, options)
    gallery.init()
  }

  function setup(gallerySelector) {
    const galleryElements = document.querySelectorAll(gallerySelector)

    let i = 0
    for (const gallery of galleryElements) {
      gallery.setAttribute('data-pswp-uid', ++i)
    }

    const hashData = parseHash()
    if (hashData.pid && hashData.gid) {
      open(hashData.pid, galleryElements[hashData.gid - 1], true, true)
    }

    for (const obj of galleryElements) {
      obj.addEventListener('click', (e) => {
        const parent = e.target.closest('.photoswipe')
        if (parent) {
          e.preventDefault()
          let index = 0

          for (const link of obj.querySelectorAll('.photoswipe')) {
            if (link === parent) {
              break
            }

            index++
          }

          open(index, obj)
        }
      })
    }
  }

  return {
    setup: setup,
    open: open,
  }
})()
