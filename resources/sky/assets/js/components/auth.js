(() => { /* global unikatSettings */
  const pad = '000'
  const random = Math.floor((Math.random() * 298) + 1)
  const img = pad.substring(0, pad.length - random.toString().length) + random.toString()
  document.body.style.backgroundImage = "url('" + unikatSettings.imgPath + img + ".jpg')"
})()
