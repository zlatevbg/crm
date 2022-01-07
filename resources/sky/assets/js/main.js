/* global Waypoint */

// http://v4-alpha.getbootstrap.com/getting-started/browsers-devices/#internet-explorer-10-in-windows-phone-8
if (navigator.userAgent.match(/IEMobile\/10\.0/)) {
  let msViewportStyle = document.createElement('style')
  msViewportStyle.appendChild(document.createTextNode('@-ms-viewport{width:auto!important}'))
  document.head.appendChild(msViewportStyle)
}

// http://v4-alpha.getbootstrap.com/getting-started/browsers-devices/#select-menu
let nua = navigator.userAgent
let isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1)
if (isAndroid) {
  const selects = document.querySelectorAll('select.form-control')
  Array.from(selects).forEach(select => {
    select.classList.remove('form-control')
    select.style.width = '100%'
  })
}

// For browsers that do not support Element.closest(), but carry support for document.querySelectorAll(), a polyfill exists:
if (window.Element && !Element.prototype.closest) {
  Element.prototype.closest = function (s) {
    const matches = (this.document || this.ownerDocument).querySelectorAll(s)
    let i
    let el = this
    do {
      i = matches.length
      while (--i >= 0 && matches.item(i) !== el) {}
    } while ((i < 0) && (el = el.parentElement))
    return el
  }
}

if (typeof HTMLCollection.prototype[Symbol.iterator] === 'undefined') {
  HTMLCollection.prototype[Symbol.iterator] = Array.prototype[Symbol.iterator]
}

if (typeof NodeList.prototype[Symbol.iterator] === 'undefined') {
  NodeList.prototype[Symbol.iterator] = Array.prototype[Symbol.iterator]
}

// from:https://github.com/jserz/js_piece/blob/master/DOM/ChildNode/remove()/remove().md
(function (arr) {
  arr.forEach(function (item) {
    if (item.hasOwnProperty('remove')) {
      return
    }

    Object.defineProperty(item, 'remove', {
      configurable: true,
      enumerable: true,
      writable: true,
      value: function remove() {
        this.parentNode.removeChild(this)
      },
    })
  })
})([Element.prototype, CharacterData.prototype, DocumentType.prototype])

$('.modal').on('shown.bs.modal', function (e) {
  const autofocus = this.querySelector('[autofocus]')
  if (autofocus) {
    autofocus.focus()
  }
})

function refreshWaypoints() {
  if (typeof Waypoint !== 'undefined') {
    Waypoint.refreshAll()
  }
}

function debounce(callback, wait, context = this) {
  let timeout = null
  let callbackArgs = null

  const later = () => callback.apply(context, callbackArgs)

  return function () {
    callbackArgs = arguments
    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

function escapeRegExp(string) {
  return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') // $& means the whole matched string
}

const headerNav = document.querySelector('#headerNav')
const navbarToggler = document.querySelector('.navbar-toggler')
for (const link of headerNav.querySelectorAll('.nav-link')) {
  link.addEventListener('mouseover', (e) => {
    if (navbarToggler.classList.contains('collapsed')) {
      for (const obj of document.querySelectorAll('.dropdown-menu')) {
        obj.classList.remove('show')
        obj.parentNode.classList.remove('show')
        const prev = obj.previousElementSibling
        if (prev) {
          prev.setAttribute('aria-expanded', false)
        }
      }

      if (link.classList.contains('dropdown-toggle')) {
        $(link).dropdown('toggle')
      }
    }
  })
}

document.querySelector('#header').addEventListener('mouseleave', () => {
  if (navbarToggler.classList.contains('collapsed')) {
    for (const obj of document.querySelectorAll('.dropdown-menu')) {
      obj.classList.remove('show')
    }

    for (const obj of document.querySelectorAll('.dropdown')) {
      obj.classList.remove('show')
    }
  }
})

for (const link of document.querySelectorAll('.dropdown-toggle')) {
  link.addEventListener('click', () => {
    location.href = link.href
  })
}

$('[data-toggle="popover"]').popover()

$('body').on('click', function (e) {
    //only buttons
    if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('.popover').length === 0) {
      $('[data-toggle="popover"]').popover('hide')
    }

    /*
    //buttons and icons within buttons
    if ($(e.target).data('toggle') !== 'popover' && $(e.target).parents('[data-toggle="popover"]').length === 0 && $(e.target).parents('.popover').length === 0) {
      $('[data-toggle="popover"]').popover('hide')
    }
    */
});
