/* global unikatSettings, Tweezer, refreshWaypoints, datatable, loadjs, CKEDITOR */
const ajax = (() => {
  let queues = {}

  $.ajaxSetup({
    processData: false, // tell jQuery not to process the data (data: new FormData(obj))
    contentType: false, // tell jQuery not to set contentType (data: new FormData(obj))
    headers: {
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
    },
  })

  bindAjaxify(document)

  $('.modal').on('show.bs.modal', function (e) {
    const obj = e.relatedTarget
    let separator = obj.getAttribute('data-action').indexOf('?') === -1 ? '?' : '&'
    let query = ''

    query += (obj.getAttribute('data-overview') !== null ? separator + 'overview=true' : '')
    separator = query.indexOf('?') === -1 ? '?' : '&'
    query += (obj.getAttribute('data-query') !== null ? separator + obj.getAttribute('data-query') : '')

    ajaxify({
      obj: obj,
      method: obj.getAttribute('data-method') || 'post',
      queue: obj.getAttribute('data-queue') || 'sync',
      alert: obj.getAttribute('data-ajax-alert') || 'form',
      spinner: obj.getAttribute('data-spinner') || 'icon',
      action: obj.getAttribute('data-action') + (obj.getAttribute('data-append-id') !== null ? '/' + datatable.selected[obj.getAttribute('data-table')] : '') + query,
      data: null,
      modal: this,
    }).then(function (data) {
    }).catch(function (error) {
    })
  })

  $('.modal').on('hidden.bs.modal', function () {
    this.querySelector('#modalTitle').innerHTML = ''
    this.querySelector('.modal-body').innerHTML = ''
  })

  function bindAjaxify(container) {
    if (!container) {
      return
    }

    for (const obj of container.querySelectorAll('[data-ajax]')) {
      let tag = obj.tagName.toLowerCase()
      let action
      let form = obj
      let method = obj.getAttribute('data-method') || 'post'
      const alert = obj.getAttribute('data-ajax-alert') || 'form'
      const queue = obj.getAttribute('data-queue') || 'sync'
      const spinner = obj.getAttribute('data-spinner') || 'icon'

      if (tag === 'form') {
        action = obj.getAttribute('action')
        method = obj.getAttribute('method').toLowerCase()
      } else if (tag === 'button') {
        action = obj.getAttribute('data-action')
      } else { // 'A'
        action = obj.href
      }

      obj.addEventListener(tag === 'form' ? 'submit' : 'click', (e) => {
        e.preventDefault()

        if (typeof CKEDITOR === 'object') {
          for (const editor of Object.keys(CKEDITOR.instances)) {
            CKEDITOR.instances[editor].updateElement()
          }
        }

        if (obj.getAttribute('data-form')) {
          form = container.querySelector('#' + obj.getAttribute('data-form'))
          tag = 'form'
        }

        let separator = action.indexOf('?') === -1 ? '?' : '&'
        let query = ''

        query += (obj.getAttribute('data-overview') !== null ? separator + 'overview=true' : '')
        separator = query.indexOf('?') === -1 ? '?' : '&'
        query += (obj.getAttribute('data-query') !== null ? separator + obj.getAttribute('data-query') : '')

        ajaxify({
          obj: obj,
          method: method,
          alert: alert,
          queue: queue,
          spinner: spinner,
          action: action + (obj.getAttribute('data-append-id') !== null ? '/' + datatable.selected[obj.getAttribute('data-table')] : '') + query,
          data: tag === 'form' ? new FormData(form) : null,
        }).then(function (data) {
        }).catch(function (error) {
        })
      })
    }
  }

  function ajaxify(params) {
    return new Promise((resolve, reject) => {
      queues[params.queue] = {}

      if (params.queue.startsWith('sync')) {
        ajaxAbort(params.queue)
      }

      if (params.obj) {
        params.tag = params.obj.tagName.toLowerCase()

        if (!params.skipLock) {
          ajaxLock(params)
        }
      }

      const jqxhr = $.ajaxq(params.queue, {
        url: params.action,
        data: params.data,
        type: params.method,
      })

      jqxhr.done((data, textStatus, jqXHR) => {
        if (jqXHR.getResponseHeader('X-Location')) { // redirect fix for IE11 & Edge
          window.location.replace(jqXHR.getResponseHeader('X-Location'))
        }

        if (data.reload) {
          window.location.reload(true)
        }

        if (data.callback) {
          const callback = eval(data.callback)
          if (typeof callback === 'function') {
            callback(data)
          }
        }

        if (params.obj) {
          if (!params.skipLock) {
            ajaxUnlock(params)
          }

          ajaxClear(params.obj, params.tag)
        }

        if (data.status) {
          params.obj.setAttribute('href', data.status.href)
          params.obj.setAttribute('title', data.status.title)

          let icon = params.obj.querySelector('[data-fa-i2svg]')
          if (icon) {
            params.obj.removeChild(icon)
          }

          params.obj.insertAdjacentHTML('afterbegin', '<i class="far fa-' + data.status.icon + ' fa-fw fa-lg"></i>')
        }

        if (data.errors && !params.skipErrors) {
          ajaxError(params, data.errors)
        }

        if (data.reset || data._old_input) {
          ajaxReset(params.obj, data)
        }

        if (data.resetEditor) {
          for (const editor of Object.keys(CKEDITOR.instances)) {
            CKEDITOR.instances[editor].setData('')
          }
        }

        if (data.datatables) {
          datatable.draw(data.datatables)
          $('[data-toggle="popover"]').popover()
        }

        if (data.closeModal) {
          $('.modal').modal('hide')
        }

        if (data.enable) {
          for (const node of document.querySelectorAll(data.enable.join(', '))) {
            node.classList.remove('disabled')
          }
        }

        if (data.disable) {
          for (const node of document.querySelectorAll(data.disable.join(', '))) {
            node.classList.add('disabled')
          }
        }

        if (data.show) {
          for (const node of document.querySelectorAll(data.show.join(', '))) {
            node.classList.remove('hidden')
            node.removeAttribute('hidden')
          }
        }

        if (data.hide) {
          for (const node of document.querySelectorAll(data.hide.join(', '))) {
            node.classList.add('hidden')
            node.setAttribute('hidden', '')
          }
        }

        if (data.success) {
          let obj = params.obj

          if (params.alert) {
              obj = obj.closest(params.alert)
          }

          ajaxSuccess(params, data.success, data.closeModal ? 'main' : params.tag)
        }

        if (params.modal) {
          setupModal(params.obj, params.modal, data.modal)
        }

        resolve(data)
      })

      jqxhr.fail((jqXHR, textStatus, errorThrown) => {
        if (textStatus !== 'abort' && !params.skipErrors) {
          if (String(jqXHR.status).startsWith(30)) { // 30* redirects
            window.location.replace(jqXHR.getResponseHeader('X-Location'))
          } else {
            if (params.obj) {
              ajaxUnlock(params)

              ajaxClear(params.obj, params.tag)
            }

            if (jqXHR.status === 422) { // Unprocessable Entity: laravel response for validation errors
              ajaxError(params, jqXHR.responseJSON.errors)
            } else {
              if (params.modal) {
                setupModal(params.obj, params.modal, {
                  title: textStatus.charAt(0).toUpperCase() + textStatus.slice(1),
                  content: errorThrown,
                })
              } else {
                ajaxError(params, textStatus.charAt(0).toUpperCase() + textStatus.slice(1) + ': ' + errorThrown)
              }
            }
          }
        } else {
          if (params.obj) {
            ajaxUnlock(params)

            ajaxClear(params.obj, params.tag)
          }
        }

        reject(errorThrown)
      })
    })
  }

  function setupModal(obj, modal, data) {
    if (typeof data === 'object') {
      modal.querySelector('#modalTitle').innerHTML = data.title
      modal.querySelector('.modal-body').innerHTML = data.content

      if (data.resources) {
        const regex = /\d{10}\./

        let resources = data.resources.split(',').map(function (resource) {
          return resource.trim()
        }).filter(function (resource) {
          return resource
        })

        const urls = resources.map(function (url) {
          return url.replace(regex, '')
        })

        for (const style of document.styleSheets) {
          if (style.href) {
            const href = style.href.replace(regex, '')
            if (urls.includes(href)) {
              delete resources[urls.indexOf(href)]
            }
          }
        }

        for (const script of document.scripts) {
          const src = script.src.replace(regex, '')
          if (urls.includes(src)) {
            delete resources[urls.indexOf(src)]
          }
        }

        resources = resources.filter(Boolean)

        if (resources.length) {
          loadjs(resources, {
            success: function () {
              if (data.callback) {
                eval(data.callback)
              }
            },
            async: false,
          })
        } else if (data.callback) {
          eval(data.callback)
        }
      } else if (data.callback) {
        eval(data.callback)
      }

      if (data.script) {
        eval(data.script)
      }

      const ids = modal.querySelector('input[name="ids"]')
      if (ids) {
        let selected = String(datatable.selected[modal.querySelector('form').getAttribute('data-table')]).split(',')

        for (const [key, value] of selected.entries()) {
          const row = document.querySelector('#\\3' + value.charAt(0) + ' ' + value.substring(1))
          if (row && row.classList.contains('protected')) {
            selected.splice(key, 1)
          }
        }

        ids.value = selected
      }

      const submit = modal.querySelector('[type="submit"]')
      if (submit) {
        bindAjaxify(modal)

        const submitComputedStyle = window.getComputedStyle(submit)
        submit.style.bottom = (parseFloat(submitComputedStyle.bottom) - (parseFloat(submit.getBoundingClientRect().height) / 2) - 4) + 'px'

        let width = 0
        const children = modal.querySelector('.modal-footer').children
        for (const node of children) {
          const nodeComputedStyle = window.getComputedStyle(node)
          width += node.offsetWidth + parseFloat(nodeComputedStyle.marginLeft) + parseFloat(nodeComputedStyle.marginRight)
        }

        submit.style.right = width + 15 + 'px' // 15 = $modal-inner-padding
      }

      if (data.fulscreen) {
        modal.classList.add('modal-fulscreen');
      }
    } else {
      modal.querySelector('#modalTitle').innerHTML = data
      modal.querySelector('.modal-body').innerHTML = data
      modal.querySelector('.btn').autofocus = true
    }
  }

  function ajaxAbort(queue) {
    if ($.ajaxq.isRunning(queue)) {
      $.ajaxq.abort(queue)
    }
  }

  function ajaxLock(params) {
    let el = params.obj

    if (params.tag === 'a') {
      el.classList.add('disabled')
    } else if (params.tag === 'button' || params.tag === 'select') {
      el.disabled = true
    } else { // form
      el = el.querySelector('[type=submit]')
      if (el) {
        el.disabled = true
        params.obj.setAttribute('tabindex', '-1')
        params.obj.focus()

        params.obj.addEventListener('keydown', (e) => {
          if (e.defaultPrevented) {
            return // Should do nothing if the default action has been cancelled
          }

          const keyCode = e.which || e.keyCode
          if (keyCode === 27 && el.classList.contains('js-submitted')) { // ESC
            e.stopPropagation()
            ajaxUnlock(params)
            ajaxAbort(params.queue)
          }
        })

        clearTimeout(queues[params.queue].timer)
        queues[params.queue].timer = window.setTimeout(() => el.classList.add('js-submitted'), 1000)
      }
    }

    if (params.spinner) {
      queues[params.queue].html = el.innerHTML
      if (params.spinner === 'replace') {
        el.innerHTML = ''
      } else {
        let icon = el.querySelector('[data-fa-i2svg]')
        if (icon) {
          el.removeChild(icon)
        }
      }
      el.insertAdjacentHTML('afterbegin', '<i class="fas fa-spinner fa-pulse' + (params.spinner === 'absolute' ? ' spinner-absolute' : '') + '"></i>')
    }
  }

  function ajaxUnlock(params) {
    let obj = params.obj
    if (params.tag === 'a') {
      obj.classList.remove('disabled')
    } else if (params.tag === 'button' || params.tag === 'select') {
      obj.disabled = false
    } else { // form
      clearTimeout(queues[params.queue].timer)
      obj = obj.querySelector('[type=submit]')
      if (obj) {
        obj.disabled = false
        obj.focus()
        obj.classList.remove('js-submitted')
      }
    }

    if (params.spinner) {
      obj.innerHTML = queues[params.queue].html
    }
  }

  function ajaxClearSuccess(obj, tag, selector = '.alert-success') {
    ajaxClear(obj, tag, selector)
  }

  function ajaxClearDanger(obj, tag, selector = '.alert-danger') {
    ajaxClear(obj, tag, selector)
  }

  function ajaxClear(obj, tag, selector = '.alert') {
    let el

    if (tag === 'form') {
      el = obj

      for (const input of obj.querySelectorAll('.form-control')) {
        input.classList.remove('is-valid')
        input.classList.remove('is-invalid')
      }

      for (const node of obj.querySelectorAll('.invalid-feedback')) {
        node.parentNode.removeChild(node)
      }
    } else {
      el = document.querySelector('main')
    }

    for (const node of el.querySelectorAll(selector)) {
      node.parentNode.removeChild(node)
    }
  }

  function ajaxSuccess(params, success, tag) {
    let el
    let refresh = false

    if (params.alert) {
      el = params.obj.closest(params.alert)
    } else if (tag === 'form') {
      el = params.obj
    } else {
      el = document.querySelector('main')
      refresh = true
    }

    el.insertAdjacentHTML('afterbegin', '<div class="alert alert-success alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="' + unikatSettings.text.close + '"><span aria-hidden="true">&times;</span></button>' + (typeof success === 'object' ? success.join('<br>') : success) + '</div>')

    scrollIntoView('#' + el.id + ' .alert')

    if (refresh) {
      $('.alert').on('closed.bs.alert', refreshWaypoints)
      refreshWaypoints()
    }
  }

  function ajaxError(params, error) {
    let el
    let refresh = false

    if (params.alert) {
      el = params.obj.closest(params.alert)
    } else if (params.tag === 'form') {
      el = params.obj
    } else {
      el = document.querySelector('main')
      refresh = true
    }

    if (typeof error === 'object') {
      if (Object.keys(error).length) {
        el.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="' + unikatSettings.text.close + '"><span aria-hidden="true">&times;</span></button>' + unikatSettings.alertErrorMessage + '</div>')
      }

      for (const key of Object.keys(error)) {
        let selector

        if (key.includes('[]')) {
          selector = 'input[name="' + key + '"]'
        } else if (key.includes('.')) {
          const keys = key.split('.')
          selector = '#input-' + keys[0] + '-' + (parseInt(keys[1]) + 1)
        } else {
          selector = '#input-' + key
        }

        const input = params.obj.querySelector(selector)

        if (input) {
          const parent = input.closest('.form-group, .form-check')
          if (parent) {
            const multiselect = parent.querySelector('.multiselect-wrapper')

            if (multiselect) {
              multiselect.querySelector('button').classList.add('is-invalid')
              multiselect.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">' + error[key].join('<br>') + '</div>')
            } else {
              input.classList.add('is-invalid')
              parent.insertAdjacentHTML('beforeend', '<div class="invalid-feedback">' + error[key].join('<br>') + '</div>')
            }
          }
        } else {
          el.querySelector('.alert').insertAdjacentHTML('beforeend', '<p class="fa-left"><i class="fas fa-exclamation-triangle"></i>' + error[key].join('<br>') + '</p>')
        }
      }
    } else {
      el.insertAdjacentHTML('afterbegin', '<div class="alert alert-danger alert-dismissible fade show" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="' + unikatSettings.text.close + '"><span aria-hidden="true">&times;</span></button>' + error + '</div>')
    }

    scrollIntoView('#' + el.id + ' .alert')

    if (refresh) {
      $('.alert').on('closed.bs.alert', refreshWaypoints)
      refreshWaypoints()
    }
  }

  function scrollIntoView(target, margin = 20) {
    const start = window.scrollY || window.pageYOffset
    let stop

    switch (typeof target) {
      case 'number':
        stop = start + target
        target = null
        break
      case 'object':
        stop = target.getBoundingClientRect().top + start
        break
      case 'string':
        target = document.querySelector(target)
        stop = target.getBoundingClientRect().top + start
        break
    }

    const end = stop - margin

    if ('scrollBehavior' in document.documentElement.style || typeof Tweezer !== 'function') {
      window.scrollTo(0, end) // target.scrollIntoView({block: 'start', behavior: 'smooth'})
    } else {
      new Tweezer({
        start: start,
        end: end,
      })
      .on('tick', v => window.scrollTo(0, v))
      .begin()
    }

    if (target) {
      target.setAttribute('tabindex', '-1')
      target.focus()
    }
  }

  function ajaxResetInput(input) {
    const type = input.type.toLowerCase()

    if (['checkbox', 'radio'].includes(type)) {
      input.checked = false
    } else if (['select-one', 'select-multiple'].includes(type)) {
      input.selectedIndex = 0 // -1 deselects everything
    } else {
      input.value = ''
      if (input.classList.contains('floatl__input')) {
        input.closest('.floatl').classList.remove('floatl--active')
      }
    }
  }

  function ajaxReset(obj, data) {
    const inputs = obj.querySelectorAll('input:not([type="hidden"]):not([type="submit"]):not([readonly]):not([disabled]), select:not([disabled]), textarea:not([readonly]):not([disabled])')
    let names

    if (data._old_input) { // Laravel JSON response: ->withInput()
      names = Object.keys(data._old_input)

      for (const input of inputs) {
        if (!names.includes(input.name)) {
          ajaxResetInput(input)
        }
      }
    }

    if (data.reset) {
      if (data.reset.only) {
        for (const name of data.reset.only) {
          ajaxResetInput(document.querySelector('#input-' + name))
        }
      } else if (data.reset.except) {
        names = Object.values(data.reset.except)

        for (const input of inputs) {
          if (!names.includes(input.name)) {
            ajaxResetInput(input)
          }
        }
      } else {
        for (const input of inputs) {
          ajaxResetInput(input)
        }
      }
    }
  }

  return {
    ajaxify: ajaxify,
    bind: bindAjaxify,
    success: ajaxSuccess,
    error: ajaxError,
    clearSuccess: ajaxClearSuccess,
  }
})()
