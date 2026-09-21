/* Admin JS */
(function () {
  "use strict";
  var t = document.getElementById("sideToggle");
  var s = document.getElementById("sidebar");

  // Overlay for mobile sidebar
  var overlay = document.createElement("div");
  overlay.className = "side-overlay";
  document.body.appendChild(overlay);
  overlay.addEventListener("click", function () {
    if (s) s.classList.remove("open");
    overlay.classList.remove("show");
  });

  if (t && s) t.addEventListener("click", function () {
    var open = s.classList.toggle("open");
    overlay.classList.toggle("show", open);
  });
  if (s) s.querySelectorAll("a").forEach(function (a) {
    a.addEventListener("click", function () {
      s.classList.remove("open");
      overlay.classList.remove("show");
    });
  });

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
