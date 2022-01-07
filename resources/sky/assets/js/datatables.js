/* global unikatSettings, ajax, Waypoint, refreshWaypoints, escapeRegExp, debounce */
const datatable = (() => {
  const DataTable = $.fn.dataTable
  const tables = []
  const waypoints = []
  const selected = {}
  let jsLink = ''

  // Handle clicks on datatable
  for (const obj of document.querySelectorAll('table[data-table]')) {
    obj.addEventListener('click', (e) => {
      var stopPropagation = true
      const table = obj.getAttribute('id')
      if (e.target.tagName.toLowerCase() === 'input' && e.target.type.toLowerCase() === 'checkbox') {
        const checkbox = e.target

        // Handle click on checkboxes
        if (checkbox.parentNode.tagName.toLowerCase() === 'th') {
          // Handle click on thead "Select all" checkbox
          handleClick(table, document.querySelectorAll('#' + table + ' tbody input[type="checkbox"]:' + (checkbox.checked ? 'not(:checked)' : 'checked')))
        } else if (checkbox.parentNode.tagName.toLowerCase() === 'td') {
          // Handle click on tbody checkbox
          handleClick(table, checkbox, false)
        }
      } else {
        const target = e.target

        // Handle click on cells
        if (target.tagName.toLowerCase() === 'th') {
          // Handle click on thead "Select all" cell
          const checkbox = target.querySelector('input[type="checkbox"]')
          if (checkbox) {
            handleClick(table, document.querySelectorAll('#' + table + ' tbody input[type="checkbox"]:' + (checkbox.checked ? 'checked' : 'not(:checked)')))
          }
        } else if (target.tagName.toLowerCase() === 'td') {
          // Handle click on tbody checkbox cell
          handleClick(table, target.parentNode.querySelector('input[type="checkbox"]'))
        } else if (target.tagName.toLowerCase() === 'button') {
          stopPropagation = false
        } else if (target.closest('.js-status')) {
          const status = target.closest('.js-status')
          e.preventDefault()
          ajax.ajaxify({
            obj: status,
            method: 'get',
            queue: 'change-status',
            action: status.href,
            spinner: 'replace',
          }).then(function (data) {
          }).catch(function (error) {
          })
        }
      }

      if (stopPropagation) {
        // Prevent click event from propagating to parent
        e.stopPropagation()
      }
    })
  }

  function draw(data) {
    for (const key of Object.keys(data)) {
      const table = tables[key]
      let row

      if (data[key].deleted) {
        table.rows([data[key].deleted]).remove().draw()
      } else if (data[key].added) {
        row = table.row.add(data[key].added).draw().node()
        $('[data-toggle="popover"]').popover()
        ajax.bind(row)
      } else if (data[key].updated) {
        row = table.row('#' + data[key].updated.id).data(data[key].updated).draw(false).node()
        $('[data-toggle="popover"]').popover()
        ajax.bind(row)
      } else {
        table.clear().rows.add(data[key].data).draw()
        const wrapper = document.querySelector('#' + key).closest('.dataTables_wrapper')
        wrapper.classList.remove('table-hidden')
      }

      selected[key] = []

      if (!data[key].select) {
        updateCheckbox(key)
      }

      if (row) {
        row.classList.add('fade-out-background')
        row.classList.add('table-success')
        row.classList.remove('table-info')

        row.addEventListener('mouseover', () => {
          row.classList.remove('fade-out-background')
        })

        window.setTimeout(() => {
          row.classList.remove('table-success')
          if (data[key].select) {
            handleClick(key, row.querySelector('input[type="checkbox"]'))
          }
        }, 1000)
      }
    }
  }

  function handleClick(table, nodes, click = true) {
    if (!nodes) {
      return
    }

    for (const node of nodes instanceof NodeList ? nodes : [nodes]) {
      if (click && !node.disabled) {
        node.checked = !node.checked
      }

      const row = node.closest('tr')
      const rowId = row.getAttribute('id')
      const index = selected[table].indexOf(rowId)

      if (node.checked && index === -1) {
        // If checkbox is checked and row ID is not in the list of selected row IDs
        selected[table].push(rowId)
      } else if (!node.checked && index !== -1) {
        // If checkbox is not checked and row ID is already in the list of selected row IDs
        selected[table].splice(index, 1)
      }

      if (node.checked) {
        row.classList.add('table-info')
      } else {
        row.classList.remove('table-info')
      }

      updateCheckbox(table)
    }
  }

  function updateCheckbox(table) {
    const wrapper = document.querySelector('#' + table + '_wrapper')
    const sticky = wrapper.previousElementSibling
    const obj = document.querySelector('#' + table)
    const allCheckboxes = obj.querySelectorAll('tbody input[type="checkbox"]')
    const checked = obj.querySelectorAll('tbody input[type="checkbox"]:checked')
    const selectAll = obj.querySelector('thead input[type="checkbox"]')

    if (checked.length === 0) {
      // If none of the checkboxes are checked
      if (selectAll) {
        selectAll.checked = false
        if ('indeterminate' in selectAll) {
          selectAll.indeterminate = false
        }
      }

      if (sticky) {
        for (const button of sticky.querySelectorAll('[data-disabled]')) {
          button.disabled = true
          button.classList.add('disabled')

          if (button.classList.contains('js-link')) {
            button.href = button.href.replace(new RegExp(escapeRegExp(jsLink) + '$'), '')
          }

          if (button.classList.contains('js-upload-single') && button.classList.contains('disabled')) {
            const input = button.querySelector('input')
            if (input) {
              input.setAttribute('disabled', '')
            }
          }
        }

        for (const button of sticky.querySelectorAll('[data-hidden]')) {
          button.setAttribute('hidden', '')

          if (button.classList.contains('js-link')) {
            button.href = button.href.replace(new RegExp(escapeRegExp(jsLink) + '$'), '')
          }
        }
      }
    } else {
      if (sticky) {
        for (const button of sticky.querySelectorAll('[data-disabled]')) {
          const count = parseInt(button.getAttribute('data-disabled'), 10)
          if (count && checked.length > count) {
            button.disabled = true
            button.classList.add('disabled')

            if (button.classList.contains('js-link')) {
              button.href = button.href.replace(new RegExp(escapeRegExp(jsLink) + '$'), '')
            }

            if (button.classList.contains('js-upload-single') && button.classList.contains('disabled')) {
              const input = button.querySelector('input')
              if (input) {
                input.setAttribute('disabled', '')
              }
            }
          } else {
            for (const node of checked instanceof NodeList ? checked : [checked]) {
              if (node.closest('tr').classList.contains('protected') && button.classList.contains('protected')) {
                break
              }

              button.disabled = false
              button.classList.remove('disabled')

              if (button.classList.contains('js-link')) {
                const id = selected[table].toString()
                const slug = document.querySelector('#\\3' + id.charAt(0) + ' ' + id.substring(1)).getAttribute('data-slug')
                const url = button.getAttribute('data-url')
                jsLink = '/' + (slug || id) + (url ? '/' + url : '')
                button.href += jsLink
              }

              if (button.classList.contains('js-upload-single')) {
                const input = button.querySelector('input')
                if (input) {
                  input.removeAttribute('disabled')
                }
              }
            }
          }
        }

        for (const button of sticky.querySelectorAll('[data-hidden]')) {
          const count = parseInt(button.getAttribute('data-hidden'), 10)
          if (count && checked.length > count) {
            button.setAttribute('hidden', '')

            if (button.classList.contains('js-link')) {
              button.href = button.href.replace(new RegExp(escapeRegExp(jsLink) + '$'), '')
            }
          } else {
            const selector = button.getAttribute('data-if')
            if (selector) {
              const id = selected[table].toString()
              const value = document.querySelector('#\\3' + id.charAt(0) + ' ' + id.substring(1) + ' ' + selector).innerHTML
              if (value) {
                sticky.querySelector('#button-' + button.getAttribute('data-value')).removeAttribute('hidden')
              } else if (button.getAttribute('data-null')) {
                sticky.querySelector('#button-' + button.getAttribute('data-null')).removeAttribute('hidden')
              }
            } else {
              button.removeAttribute('hidden')
            }

            if (button.classList.contains('js-link')) {
              const id = selected[table].toString()
              const slug = document.querySelector('#\\3' + id.charAt(0) + ' ' + id.substring(1)).getAttribute('data-slug')
              const url = button.getAttribute('data-url')
              jsLink = '/' + (slug || id) + (url ? '/' + url : '')
              button.href += jsLink
            }
          }
        }
      }

      if (selectAll) {
        if (checked.length === allCheckboxes.length) {
          // If all of the checkboxes are checked
          selectAll.checked = true
          if ('indeterminate' in selectAll) {
            selectAll.indeterminate = false
          }
        } else {
          // If only some of the checkboxes are checked
          selectAll.checked = true
          if ('indeterminate' in selectAll) {
            selectAll.indeterminate = true
          }
        }
      }
    }
  }

  function columns(table, param) {
    const data = param.columns
    const columns = []

    for (const value of data) {
      let render = null

      if (!value.hidden) {
        if (value.checkbox) {
          render = (data, type, full, meta) => '<input type="checkbox">'
        } else if (value.render) {
          render = {
            display: 'display',
          }

          for (const type of Object.values(value.render)) {
            render[type] = type
          }
        }

        columns.push({
          data: value.id,
          title: (value.checkbox ? '<input type="checkbox" value="1" name="check-' + table + '" id="input-check-' + table + '">' : value.name),
          searchable: value.search,
          orderable: typeof value.order !== 'undefined' ? value.order : true,
          className: value.class,
          width: value.width ? value.width : (value.checkbox ? '1.25em' : null),
          render: render,
          type: value.type,
          // type: 'custom-name',
        })
      }
    }

    return columns
  }

  function setup(params) {
    /*$.fn.dataTable.ext.type.order['custom-name-pre'] = function (d) {
      switch (d) {
        case 'xxx': return 1
        case 'yyy': return 2
        case 'zzz': return 3
      }
      return 0
    }*/

    for (const [table, param] of Object.entries(params)) {
      if (param.data) {
        const count = param.data.length
        const size = (count <= 100 ? 's' : (count <= 1000 ? 'm' : 'l'))
        selected[table] = []

        let checkbox = false
        let disabled = false
        let protect = false
        for (const value of param.columns) {
          if (value.checkbox) {
            checkbox = true

            if (value.disabled) {
              disabled = value.disabled
            }

            if (value.protected) {
              protect = value.protected
            }

            break
          }
        }

        tables[table] = $('#' + table).DataTable({
          dom: param.options.dom ? param.options.dom : '<"card-header"lf><"card-block table-responsive"tr><"card-footer"ip>',
          renderer: 'bootstrap',
          classes: {
            sTable: 'dataTable table table-hover ' + (checkbox ? 'table-checkbox ' : '') + (param.options.class ? param.options.class : ''),
            sWrapper: 'dataTables_wrapper card ' + (param.options.wrapperClass ? param.options.wrapperClass : ''),
            sFilter: 'dataTables_filter form-inline',
            sFilterInput: 'form-control',
            sLength: 'dataTables_length form-inline',
            sLengthSelect: 'form-control mr-2 ml-2',
            sPageButton: 'paginate_button page-item',
          },
          stateSave: true,
          stateDuration: -1,
          processing: true,
          retrieve: true,
          deferRender: true,
          rowReorder: {
            selector: '.reorder',
            dataSrc: 'order',
          },
          autoWidth: false,
          stripeClasses: [],
          rowId: 'id',
          language: unikatSettings.datatables.language,
          paging: count > 10,
          searchDelay: 100,
          search: {
            search: unikatSettings.datatablesSearch,
          },
          pagingType: size === 's' ? 'numbers' : (size === 'm' ? 'simple_numbers' : (size === 'l' ? 'full_numbers' : 'simple')),
          pageLength: size === 's' ? 25 : (size === 'm' ? 50 : (size === 'l' ? 100 : 10)),
          lengthMenu: unikatSettings.datatables.lengthOptions[size],
          order: param.order ? param.order : [],
          data: param.data ? param.data : null,
          columns: columns(table, param),
          createdRow: (row, data, dataIndex) => {
            if (data.slug) {
              row.setAttribute('data-slug', data.slug)
            }

            if (param.options.priorities && data[param.options.priorities]) {
              row.classList.add('priorities')
              row.querySelector('.task-priority').classList.add('priority' + data[param.options.priorities])
            }

            if (param.options.cellBackground && data[param.options.cellBackground]) {
              row.classList.add('background')
              row.querySelector('.bg-cell-color').classList.add(data[param.options.cellBackground])
            }

            if (param.options.rowBackground && data[param.options.rowBackground]) {
              row.classList.add('highlighted')
            }

            if (checkbox) {
              const id = parseInt(row.getAttribute('id'), 10)

              if (id === disabled || (row.querySelector('.status') && data['deleted_at'])) {
                row.classList.add('disabled')
                let input = row.querySelector('input[type="checkbox"]')
                if (input) {
                  input.disabled = true
                }
              }

              if (id === protect) {
                row.classList.add('protected')
              }

              if (selected[table].indexOf(row.id) !== -1) {
                row.querySelector('input[type="checkbox"]').checked = true
                row.classList.add('table-info')
              }
            }
          },
          drawCallback: checkbox ? (settings) => updateCheckbox(table) : null,
          footerCallback: param.options.footer ? function (tfoot, data, start, end, display) {
            if (data.length) {
              const api = this.api()
              let number
              let th

              for (const [index, column] of Object.entries(param.columns)) {
                if (column.footer) {
                  th = tfoot.getElementsByTagName('th')[index]

                  if (column.footer.function === 'sum') {
                    number = api.column(index).data().reduce(function (a, b) {
                      return a + (b ? Number(b.toString().replace(/[^0-9\.-]+/g, '')) : 0)
                    }, 0) // toFixed(2) // .toLocaleString(undefined, { style: 'currency', currency: 'EUR', minimumFractionDigits: 2 })

                    if (column.footer.currency) {
                      th.textContent = new Intl.NumberFormat('en-US', { style: 'currency', currency: 'EUR' }).format(number).replace(/,/g, ' ') // $(api.column(index).footer()).find('span') // tfoot.querySelectorAll('*')[index]
                    } else {
                      th.textContent = number
                    }
                  } else if (column.footer.function === 'count') {
                    number = api.column(index).data().count()
                    th.textContent = number
                  }
                }
              }
            }
          } : null,
        })

        // search.search option above is not working ?
        if (unikatSettings.datatablesSearch) {
          tables[table].search(unikatSettings.datatablesSearch).draw()
        }

        tables[table].on('row-reordered', function (e, diff, edit) {
          if (diff.length) {
            const formData = new FormData()
            for (const [row, value] of Object.entries(edit.values)) {
              formData.append(row, value)
            }

            ajax.ajaxify({
              method: 'post',
              queue: 'reorder',
              action: e.target.getAttribute('data-route'),
              data: formData,
            }).then(function (data) {
            }).catch(function (error) {
            })
          }
        })

        datatable.sticky(table)
        ajax.bind(document.querySelector('#' + table))
      }
    }
  }

  function sticky(table) {
    if (!waypoints[table]) {
      const sticky = document.querySelector('#' + table + '-sticky-wrapper')
      if (sticky) {
        /*if (sticky.offsetHeight) {
          sticky.style.height = sticky.offsetHeight + 'px'
        }*/

        /*const header = sticky.querySelector('.datatable-sticky-header')
        header.classList.remove('pinned')
        header.classList.remove('unpinned')
        */

        // ajax.bind(sticky) // not needed

        waypoints[table] = new Waypoint({
          element: sticky,
          handler: function (direction) {
            if (direction === 'down') {
              this.element.firstElementChild.classList.add('pinned')
            } else {
              this.element.firstElementChild.classList.remove('pinned')
            }
          },
        })

        const wrapper = sticky.nextElementSibling
        waypoints[table + '-wrapper'] = new Waypoint({
          element: wrapper,
          handler: function (direction) {
            sticky.firstElementChild.classList.toggle('unpinned')
          },
          offset: function () {
            const bottom = wrapper.querySelector('.card-footer') || wrapper.querySelector('.card-block') || {offsetHeight: 0}
            const offset = wrapper.offsetHeight - wrapper.offsetTop - bottom.offsetHeight + sticky.offsetHeight
            return -offset
          },
        })
      }
    }/* else {
      refreshWaypoints()
    }*/
  }

  $(document).on('preInit.dt', function (e, settings) {
    const api = new DataTable.Api(settings)
    const table = api.table().node()
    const tableId = table.getAttribute('id')
    const wrapper = document.querySelector('#' + tableId).closest('.dataTables_wrapper')
    const filter = wrapper.querySelector('.dataTables_filter input')

    const event = new MouseEvent('click', {
      view: window,
      bubbles: true,
      cancelable: true,
    })

    $(filter).off('keyup.DT input.DT') // disable global search events except: search.DT paste.DT cut.DT
    $(filter).on('keyup.DT input.DT', debounce(function (e) {
      const checkboxes = document.querySelector('#' + tableId + ' tbody input[type="checkbox"]:checked')
      if (checkboxes) {
        checkboxes.dispatchEvent(event)
      }
      selected[tableId] = []
      api.search(e.target.value).draw()
    }, 100))
  })

  $(document).on('draw.dt', (e, settings) => {
    const api = new DataTable.Api(settings)
    const table = api.table().node()
    const tableId = table.getAttribute('id')

    handleClick(tableId, document.querySelectorAll('#' + tableId + ' tbody input[type="checkbox"]:checked'))
    $('[data-toggle="popover"]', document.querySelector('#' + tableId)).popover()
  })

  DataTable.ext.renderer.pageButton.bootstrap = (settings, host, idx, buttons, page, pages) => {
    const api = new DataTable.Api(settings)
    const lang = settings.oLanguage.oPaginate
    const aria = settings.oLanguage.oAria.paginate || {}
    let counter = 0

    const attach = (container, buttons) => {
      const clickHandler = (e) => {
        e.preventDefault()

        if (!e.currentTarget.classList.contains('disabled') && api.page() !== e.data.action) {
          api.page(e.data.action).draw('page')
          refreshWaypoints()
        }
      }

      for (const button of buttons) {
        if (Array.isArray(button)) {
          attach(container, button)
        } else {
          let btnDisplay
          let btnClass

          switch (button) {
            case 'ellipsis':
              btnDisplay = '&#x2026;'
              btnClass = 'disabled'
              break
            case 'first':
              btnDisplay = lang.sFirst
              btnClass = button + (page > 0 ? '' : ' disabled')
              break
            case 'previous':
              btnDisplay = lang.sPrevious
              btnClass = button + (page > 0 ? '' : ' disabled')
              break
            case 'next':
              btnDisplay = lang.sNext
              btnClass = button + (page < pages - 1 ? '' : ' disabled')
              break
            case 'last':
              btnDisplay = lang.sLast
              btnClass = button + (page < pages - 1 ? '' : ' disabled')
              break
            default:
              btnDisplay = button + 1
              btnClass = page === button ? 'active' : ''
              break
          }

          if (btnDisplay) {
            const li = document.createElement('li')
            if (idx === 0 && typeof button === 'string') {
              li.setAttribute('id', settings.sTableId + '_' + button)
            }
            li.setAttribute('class', settings.oClasses.sPageButton + ' ' + btnClass)
            li.innerHTML = '<a href="#" class="page-link" aria-controls="' + settings.sTableId + '"' + (button in aria ? ' aria-label="' + aria[button] + '"' : '') + ' data-dt-idx="' + counter + '" tabindex="' + settings.iTabIndex + '">' + btnDisplay + '</a>'

            container.appendChild(li)

            settings.oApi._fnBindAction(li, { action: button }, clickHandler)

            counter++
          }
        }
      }
    }

    const activeEl = document.activeElement.getAttribute('data-dt-idx')

    const ul = document.createElement('ul')
    ul.setAttribute('class', 'pagination')
    host.innerHTML = ''
    host.appendChild(ul)

    attach(ul, buttons)

    if (activeEl !== null) {
      host.querySelector('[data-dt-idx="' + activeEl + '"]').focus()
    }
  }

  return {
    setup: setup,
    sticky: sticky,
    selected: selected,
    draw: draw,
  }
})()
