/* Pallottine Nigerian Youth — frontend JS (no dependencies) */
(function () {
  "use strict";

  var fmtNaira = function (n) {
    n = parseFloat(n) || 0;
    return "₦" + n.toLocaleString("en-NG", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  };

  /* ---------- Mobile nav ---------- */
  var toggle = document.getElementById("navToggle");
  var nav = document.getElementById("mainNav");
  if (toggle && nav) {
    toggle.addEventListener("click", function () {
      var open = nav.classList.toggle("open");
      toggle.setAttribute("aria-expanded", open ? "true" : "false");
    });
    nav.querySelectorAll("a").forEach(function (a) {
      a.addEventListener("click", function () { nav.classList.remove("open"); });
    });
  }

  /* ---------- Live chat panel ---------- */
  var chatBtn = document.getElementById("chatBtn");
  var chatPanel = document.getElementById("chatPanel");
  var chatClose = document.getElementById("chatClose");
  if (chatBtn && chatPanel) {
    chatBtn.addEventListener("click", function () {
      chatPanel.hidden = !chatPanel.hidden;
    });
    if (chatClose) chatClose.addEventListener("click", function () { chatPanel.hidden = true; });
  }

  /* ---------- Copy-to-clipboard ---------- */
  document.querySelectorAll("[data-copy]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var text = btn.getAttribute("data-copy") || "";
      var done = function () {
        var old = btn.textContent;
        btn.textContent = "Copied ✓";
        setTimeout(function () { btn.textContent = old; }, 1600);
      };
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(done, done);
      } else {
        var ta = document.createElement("textarea");
        ta.value = text; document.body.appendChild(ta); ta.select();
        try { document.execCommand("copy"); } catch (e) {}
        document.body.removeChild(ta); done();
      }
    });
  });

  /* ---------- Toast ---------- */
  var toastEl = null, toastTimer = null;
  function toast(msg) {
    if (!toastEl) {
      toastEl = document.createElement("div");
      toastEl.className = "toast";
      document.body.appendChild(toastEl);
    }
    toastEl.textContent = msg;
    toastEl.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.classList.remove("show"); }, 2400);
  }

  /* ---------- Cart badge ---------- */
  function setBadge(count) {
    var badge = document.getElementById("cartBadge");
    if (!badge) return;
    badge.textContent = count;
    badge.classList.remove("pop");
    void badge.offsetWidth; /* restart animation */
    badge.classList.add("pop");
  }

  /* ---------- Mini cart drawer ---------- */
  var mini = document.getElementById("miniCart");
  var overlay = document.getElementById("miniOverlay");
  var miniItems = document.getElementById("miniItems");
  var miniSub = document.getElementById("miniSub");
  function openMini() {
    if (!mini) return;
    mini.classList.add("show");
    if (overlay) overlay.classList.add("show");
    document.body.style.overflow = "hidden";
  }
  function closeMini() {
    if (!mini) return;
    mini.classList.remove("show");
    if (overlay) overlay.classList.remove("show");
    document.body.style.overflow = "";
  }
  var miniClose = document.getElementById("miniClose");
  if (miniClose) miniClose.addEventListener("click", closeMini);
  if (overlay) overlay.addEventListener("click", closeMini);
  document.addEventListener("keydown", function (e) { if (e.key === "Escape") closeMini(); });

  function esc(s) {
    return String(s == null ? "" : s).replace(/[&<>"']/g, function (c) {
      return { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[c];
    });
  }
  function renderMini(lines, subtotal) {
    if (!miniItems) return;
    if (!lines || !lines.length) {
      miniItems.innerHTML = '<p class="hint" style="text-align:center;padding:1rem">Your cart is empty.</p>';
    } else {
      miniItems.innerHTML = lines.map(function (l) {
        return '<div class="mini-item">' +
          '<img src="' + esc(l.image) + '" alt="">' +
          "<div><strong>" + esc(l.name) + "</strong><span>" + fmtNaira(l.price) + " × " + l.qty + "</span></div>" +
          "<b>" + fmtNaira(l.line_total) + "</b></div>";
      }).join("");
    }
    if (miniSub) miniSub.textContent = fmtNaira(subtotal);
  }

  /* ---------- Quantity steppers (product + cart) ---------- */
  document.querySelectorAll(".qty-ctl").forEach(function (ctl) {
    var input = ctl.querySelector("input");
    if (!input) return;
    ctl.querySelectorAll("button").forEach(function (b) {
      b.addEventListener("click", function () {
        var v = parseInt(input.value || "1", 10);
        if (b.dataset.act === "inc") v = Math.min(99, v + 1);
        else v = Math.max(parseInt(input.min || "1", 10), v - 1);
        input.value = v;
        input.dispatchEvent(new Event("change", { bubbles: true }));
      });
    });
  });

  /* ---------- Live total on product page (unit price × qty) ---------- */
  document.querySelectorAll("[data-unit-price]").forEach(function (box) {
    var unit = parseFloat(box.dataset.unitPrice) || 0;
    var input = box.querySelector('input[name="qty"]');
    var out = box.querySelector("[data-live-total]");
    if (!input || !out) return;
    var sync = function () {
      var q = Math.max(1, parseInt(input.value || "1", 10));
      out.textContent = fmtNaira(unit * q);
    };
    input.addEventListener("change", sync);
    input.addEventListener("input", sync);
    sync();
  });

  /* ---------- Cart page: live line totals + subtotal + auto-save ---------- */
  var cartForm = document.getElementById("cartForm");
  if (cartForm) {
    var subEl = document.getElementById("cartSubtotal");
    var saveTimer = null;
    var recalc = function () {
      var sub = 0;
      cartForm.querySelectorAll("[data-cart-row]").forEach(function (row) {
        var price = parseFloat(row.dataset.price) || 0;
        var input = row.querySelector("[data-cart-qty]");
        var cell = row.querySelector("[data-line-total]");
        var q = Math.max(0, parseInt(input.value || "0", 10));
        var line = price * q;
        sub += line;
        if (cell) cell.textContent = fmtNaira(line);
      });
      if (subEl) subEl.textContent = fmtNaira(sub);
    };
    var autoSave = function () {
      clearTimeout(saveTimer);
      saveTimer = setTimeout(function () {
        var fd = new FormData(cartForm);
        fd.append("ajax", "1");
        fetch("cart.php", { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } })
          .then(function (r) { return r.json(); })
          .then(function (data) {
            if (data && typeof data.count !== "undefined") setBadge(data.count);
          })
          .catch(function () { /* stays saved on next checkout anyway */ });
      }, 600);
    };
    cartForm.querySelectorAll("[data-cart-qty]").forEach(function (input) {
      input.addEventListener("change", function () { recalc(); autoSave(); });
      input.addEventListener("input", recalc);
    });
  }

  /* ---------- Add to cart: NEVER leaves the page ---------- */
  document.querySelectorAll("form.js-add-cart").forEach(function (form) {
    form.addEventListener("submit", function (ev) {
      ev.preventDefault(); /* stay on shop / product page */
      var btn = form.querySelector('button[type="submit"]');
      var oldLabel = btn ? btn.innerHTML : "";
      if (btn) { btn.disabled = true; btn.innerHTML = "Adding…"; }
      var fd = new FormData(form);
      fd.append("ajax", "1");
      fetch(form.getAttribute("action") || "cart.php", {
        method: "POST",
        body: fd,
        headers: { "X-Requested-With": "XMLHttpRequest" }
      })
        .then(function (r) { return r.text().then(function (t) { return { ok: r.ok, text: t }; }); })
        .then(function (res) {
          var data = null;
          try { data = JSON.parse(res.text); } catch (e) { data = null; }
          if (!data) throw new Error("bad-response");
          if (!data.ok) { toast(data.message || "Could not add to cart."); return; }
          setBadge(data.count);
          renderMini(data.lines || [], data.subtotal || 0);
          openMini();
          toast(data.message || "Added to cart ✓");
          if (btn) {
            btn.classList.add("added");
            btn.innerHTML = "Added ✓";
            setTimeout(function () { btn.classList.remove("added"); btn.innerHTML = oldLabel; }, 1800);
          }
        })
        .catch(function () {
          toast("Network error — please try again.");
        })
        .finally(function () {
          if (btn) btn.disabled = false;
        });
    });
  });

  /* ---------- Receipt file input label ---------- */
  var receipt = document.getElementById("receipt");
  var receiptLabel = document.getElementById("receiptLabel");
  if (receipt && receiptLabel) {
    receipt.addEventListener("change", function () {
      receiptLabel.textContent = receipt.files.length ? receipt.files[0].name : "Choose receipt image (JPG/PNG)…";
    });
  }

  /* ---------- Delivery fee toggle on checkout ---------- */
  var zoneSelect = document.getElementById("zoneSelect");
  var feeEl = document.getElementById("deliveryFee");
  var totalEl = document.getElementById("grandTotal");
  var subEl2 = document.getElementById("subTotal");
  if (zoneSelect && feeEl && totalEl && subEl2) {
    var sub = parseFloat(subEl2.dataset.value || "0");
    var sync = function () {
      var opt = zoneSelect.options[zoneSelect.selectedIndex];
      var fee = parseFloat((opt && opt.dataset.fee) || "0");
      feeEl.textContent = fmtNaira(fee);
      totalEl.textContent = fmtNaira(sub + fee);
    };
    zoneSelect.addEventListener("change", sync);
    sync();
  }
})();
