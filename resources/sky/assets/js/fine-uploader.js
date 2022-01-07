const uploader = (() => { /* global unikatSettings, qq, ajax, datatable */
  init()

  function init() {
    for (const button of document.querySelectorAll('[data-upload]')) {
      if (button.offsetParent) { // element is visible
        const params = {
          button: button,
          url: button.getAttribute('data-action'),
          multiple: button.getAttribute('data-multiple'),
          isFile: button.getAttribute('data-file'),
        }

        setup(params)
      }
    }
  }

  function setup(params) {
    const config = {
      // debug: true,
      button: params.button,
      multiple: params.multiple,
      allowMultipleItems: params.multiple,
      maxConnections: 1, // there are problems with multiple connections: the files are not uploaded or the records in the DB are duplicated.
      chunking: {
        enabled: true,
        concurrent: {
          enabled: true,
        },
        success: {
          endpoint: params.url + '?done',
        },
      },
      paste: {
        targetElement: document,
      },
      request: {
        endpoint: params.url,
        customHeaders: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
      },
      resume: {
        enabled: true,
      },
      retry: {
        enableAuto: true,
      },
      validation: {
        allowedExtensions: params.isFile ? unikatSettings.fineUploader.fileExtensions : unikatSettings.fineUploader.imageExtensions,
        stopOnFirstInvalidFile: false,
      },
      text: {
        defaultResponseError: unikatSettings.fineUploader.defaultResponseError,
        fileInputTitle: unikatSettings.fineUploader.fileInputTitle,
      },
      messages: {
        emptyError: unikatSettings.fineUploader.emptyError,
        maxHeightImageError: unikatSettings.fineUploader.maxHeightImageError,
        maxWidthImageError: unikatSettings.fineUploader.maxWidthImageError,
        minHeightImageError: unikatSettings.fineUploader.minHeightImageError,
        minWidthImageError: unikatSettings.fineUploader.minWidthImageError,
        minSizeError: unikatSettings.fineUploader.minSizeError,
        noFilesError: unikatSettings.fineUploader.noFilesError,
        onLeave: unikatSettings.fineUploader.onLeave,
        retryFailTooManyItemsError: unikatSettings.fineUploader.retryFailTooManyItemsError,
        sizeError: unikatSettings.fineUploader.sizeError,
        tooManyItemsError: unikatSettings.fineUploader.tooManyItemsError,
        typeError: unikatSettings.fineUploader.typeError,
        unsupportedBrowserIos8Safari: unikatSettings.fineUploader.unsupportedBrowserIos8Safari,
      },
      callbacks: {
        onSubmit: function (id, name) {},
        onSubmitted: function (id, name) {
          if (params.button.classList.contains('js-upload-single')) {
            this.setParams({
              id: datatable.selected[params.button.getAttribute('data-table')],
            })
            // uploader.setEndpoint(params.url + (params.url.indexOf('?') === -1 ? '?' : '&') + 'id=' + datatable.selected[params.button.getAttribute('data-table')])
          }

          ajax.clearSuccess()
          const container = document.querySelector('#upload-progress-bar-container')

          if (!container) {
            params.button.closest('.datatable-sticky-header').insertAdjacentHTML('beforeend', '<div id="upload-progress-bar-container" class="qq-total-progress-bar-container-selector progress"><div id="upload-progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" class="qq-total-progress-bar-selector progress-bar bg-success progress-bar-striped progress-bar-animated"></div></div>')
          }
        },
        onTotalProgress: function (totalUploadedBytes, totalBytes) {
          let progress
          let speed = ''
          const minSamples = 6
          const maxSamples = 20
          const uploadSpeeds = []
          const progressPercent = (totalUploadedBytes / totalBytes).toFixed(2)
          const totalSize = formatSize(totalBytes, this._options.text.sizeSymbols)
          const totalUploadedSize = formatSize(totalUploadedBytes, this._options.text.sizeSymbols)

          uploadSpeeds.push({
            totalUploadedBytes: totalUploadedBytes,
            currentTime: new Date().getTime(),
          })

          if (uploadSpeeds.length > maxSamples) {
            uploadSpeeds.shift()
          }

          if (uploadSpeeds.length >= minSamples) {
            const firstSample = uploadSpeeds[0]
            const lastSample = uploadSpeeds[uploadSpeeds.length - 1]
            const progressBytes = lastSample.totalUploadedBytes - firstSample.totalUploadedBytes
            const progressTimeMS = lastSample.currentTime - firstSample.currentTime
            const Mbps = ((progressBytes * 8) / (progressTimeMS / 1000) / (1000 * 1000)).toFixed(2) // megabits per second
            // const MBps = (progressBytes / (progressTimeMS / 1000) / (1024 * 1024)).toFixed(2); // megabytes per second

            if (Mbps > 0) {
              speed = ' / ' + Mbps + ' Mbps'
            }
          }

          if (isNaN(progressPercent)) {
            progress = '0%'
          } else {
            progress = (progressPercent * 100).toFixed() + '%'
          }

          const progressBar = document.querySelector('#upload-progress-bar')
          if (progressBar) {
            progressBar.style.width = progress
            progressBar.textContent = progress + ' (' + totalUploadedSize + ' ' + unikatSettings.fineUploader.of + ' ' + totalSize + speed + ')'
          }
        },
        onComplete: function (id, name, responseJSON, xhr) {
          if (responseJSON.data) {
            datatable.draw(responseJSON.data)
          }
        },
        onAllComplete: function (succeeded, failed) {
          document.querySelector('#upload-progress-bar-container').remove()

          if (failed.length > 0) {
            let msg = '<strong>' + unikatSettings.fineUploader.failedUploads + '</strong><br>'
            for (const id of failed) {
              msg += this.getName(id) + '<br>'
            }

            ajax.error(null, msg)
          }

          if (succeeded.length > 0) {
            ajax.success(null, unikatSettings.fineUploader.uploadComplete)
          }
        },
        onPasteReceived: function (blob) {
          if (!params.button.offsetParent) { // button not visible
            return false
          }
        },
        onError: function (id, name, errorReason, xhr) {
          /*
          if (xhr && xhr.responseText) {
            const response = JSON.parse(xhr.responseText)
            if (response.refresh) { // VerifyCsrfToken exception
              this.cancelAll()
              window.location.reload(true)
            }
          }
          */

          if (name) {
            ajax.error(null, '[<strong>' + name + '</strong>] ' + errorReason)
          }
        },
      },
    }

    const uploader = new qq.FineUploaderBasic(config)

    if (config.button.classList.contains('js-upload-single') && config.button.classList.contains('disabled')) {
      const input = config.button.querySelector('input')
      if (input) {
        input.setAttribute('disabled', '')
      }
    }

    const dragAndDropModule = new qq.DragAndDrop({
      allowMultipleItems: params.multiple,
      dropZoneElements: [document.body],
      classes: {
        dropActive: 'qq-upload-drop-area-active',
      },
      callbacks: {
        processingDroppedFiles: function () {},
        processingDroppedFilesComplete: function (files, dropTarget) {
          uploader.addFiles(files)
        },
        dropError: function (errorCode, errorRelatedData) {
          if (errorCode === 'tooManyFilesError') {
            ajax.error(null, unikatSettings.fineUploader.multipleItemsError.replace(/\{itemLimit\}/g, 1))
          }
        },
      },
    })
  }

  function formatSize(bytes, sizeSymbols) {
    var i = -1
    do {
      bytes = bytes / 1024
      i++
    } while (bytes > 1023)

    return Math.max(bytes, 0.1).toFixed(1) + sizeSymbols[i]
  }

  return {
    init: init,
  }
})()
