(() => { /* global Floatl */
  const floatl = document.querySelectorAll('.floatl')
  for (const node of floatl) {
    new Floatl(node)
  }
})()
