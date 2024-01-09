document
  .getElementsByName("postal_code")[0]
  .addEventListener("change", (ev) => {
    ev.target.value = ev.target.value.replace(/\s+/g, "").substring(0, 6)
  })
