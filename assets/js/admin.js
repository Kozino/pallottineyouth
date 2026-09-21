/* Admin JS */
(function () {
  "use strict";
  var t = document.getElementById("sideToggle");
  var s = document.getElementById("sidebar");
  if (t && s) t.addEventListener("click", function () { s.classList.toggle("open"); });

  // Confirm deletes
  document.querySelectorAll("[data-confirm]").forEach(function (el) {
    el.addEventListener("click", function (ev) {
      if (!confirm(el.getAttribute("data-confirm") || "Are you sure?")) ev.preventDefault();
    });
  });

  // Auto-slug from title
  var title = document.getElementById("f-title");
  var slug = document.getElementById("f-slug");
  if (title && slug && !slug.value) {
    title.addEventListener("input", function () {
      slug.value = title.value.toLowerCase().trim().replace(/[^a-z0-9]+/g, "-").replace(/^-+|-+$/g, "");
    });
  }
})();
