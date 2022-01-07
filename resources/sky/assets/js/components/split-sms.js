const splitSms = ((splitter) => {
  const initialize = () => {
    const nodes = document.querySelectorAll('.split-sms')
    for (const node of nodes) {
      const message = node.querySelector('.split-sms-message')
      const count = node.querySelector('.split-sms-count')
      const length = node.querySelector('.split-sms-length')

      node.addEventListener('keydown', (e) => {
        update(message.value, count, length)
      })

      node.addEventListener('keyup', (e) => {
        update(message.value, count, length)
      })

      update(message.value, count, length)
    }
  }

  const update = (value, count, length) => {
    const info = splitter.split(value)
    count.textContent = info.parts.length
    length.textContent = info.remainingInPart
  }

  return {
    initialize: initialize,
  }
})(window.splitter)
