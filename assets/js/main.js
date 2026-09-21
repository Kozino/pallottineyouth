/* Pallottine Nigerian Youth — frontend JS (no dependencies) */
(function () {
  "use strict";

  // Mobile nav
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

  // Live chat panel
  var chatBtn = document.getElementById("chatBtn");
  var chatPanel = document.getElementById("chatPanel");
  var chatClose = document.getElementById("chatClose");
  if (chatBtn && chatPanel) {
    chatBtn.addEventListener("click", function () {
      chatPanel.hidden = !chatPanel.hidden;
    });
    if (chatClose) chatClose.addEventListener("click", function () { chatPanel.hidden = true; });
  }

  // Copy-to-clipboard buttons
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

  // Quantity steppers
  document.querySelectorAll(".qty-ctl").forEach(function (ctl) {
    var input = ctl.querySelector("input");
    ctl.querySelectorAll("button").forEach(function (b) {
      b.addEventListener("click", function () {
        var v = parseInt(input.value || "1", 10);
        if (b.dataset.act === "inc") v = Math.min(99, v + 1);
        else v = Math.max(1, v - 1);
        input.value = v;
      });
    });
  });

  // Add to cart (AJAX, falls back to normal submit)
  document.querySelectorAll("form.js-add-cart").forEach(function (form) {
    form.addEventListener("submit", function (ev) {
      ev.preventDefault();
      var fd = new FormData(form);
      fd.append("ajax", "1");
      fetch(form.action, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          var badge = document.getElementById("cartBadge");
          if (badge && typeof data.count !== "undefined") badge.textContent = data.count;
          toast(data.message || "Added to cart ✓");
        })
        .catch(function () { form.submit(); });
    });
  });

  // Receipt file input label
  var receipt = document.getElementById("receipt");
  var receiptLabel = document.getElementById("receiptLabel");
  if (receipt && receiptLabel) {
    receipt.addEventListener("change", function () {
      receiptLabel.textContent = receipt.files.length ? receipt.files[0].name : "Choose receipt image (JPG/PNG)…";
    });
  }

  // Delivery fee toggle on checkout (form select drives the summary)
  var zoneSelect = document.getElementById("zoneSelect");
  var feeEl = document.getElementById("deliveryFee");
  var totalEl = document.getElementById("grandTotal");
  var subEl = document.getElementById("subTotal");
  if (zoneSelect && feeEl && totalEl && subEl) {
    var sub = parseFloat(subEl.dataset.value || "0");
    var fmt = function (n) {
      return "₦" + n.toLocaleString("en-NG", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var sync = function () {
      var opt = zoneSelect.options[zoneSelect.selectedIndex];
      var fee = parseFloat((opt && opt.dataset.fee) || "0");
      feeEl.textContent = fmt(fee);
      totalEl.textContent = fmt(sub + fee);
    };
    zoneSelect.addEventListener("change", sync);
    sync();
  }

  // Simple toast
  function toast(msg) {
    var t = document.createElement("div");
    t.textContent = msg;
    t.style.cssText = "position:fixed;left:50%;bottom:26px;transform:translateX(-50%);background:#0B1F4B;color:#fff;padding:.7rem 1.3rem;border-radius:999px;font-weight:700;z-index:99;box-shadow:0 10px 30px rgba(0,0,0,.3)";
    document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 2200);
  }
})();
