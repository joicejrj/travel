/**
 * jSelect - Convert <select> elements into Bootstrap button groups
 * Supports:
 *   - Single or multiple selection
 *   - Dynamic button colors (via data-btn attribute)
 *   - Custom/Other option (shows text input)
 *
 * Usage:
 *   <select class="jselect form-select" multiple>
 *     <option value="Call" data-btn="primary">Call</option>
 *     <option value="Email" data-btn="success">Email</option>
 *     <option value="Meeting" data-btn="warning">Meeting</option>
 *     <option value="Other" data-btn="secondary" data-other="true">Other</option>
 *   </select>
 */
document.addEventListener("DOMContentLoaded", function () {

  function initJSelect($select) {
    // ✅ prevent double init
    if ($select.dataset.jselectInitialized === "true") return;
    $select.dataset.jselectInitialized = "true";

    const multiple = $select.hasAttribute("multiple");
    const name = $select.getAttribute("name") || "";
    const itype = $select.getAttribute("data-type") || "text";
    const options = Array.from($select.options);
    const btncls = $select.dataset.class ? 'btn-' + $select.dataset.class : "btn-outline-primary";

    // wrapper
    const wrapper = document.createElement("div");
    wrapper.className = "jselect-wrapper mb-2";
    if ($select.id) wrapper.setAttribute("data-for", $select.id);

    // hidden for single
    // if (!multiple) {
    //   const hidden = document.createElement("input");
    //   hidden.type = "hidden";
    //   hidden.name = name;
    //   hidden.value = $select.value || "";
    //   wrapper.appendChild(hidden);
    // }
    if (!multiple) {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = name;
      hidden.value = $select.value || "";
      wrapper.appendChild(hidden);

      // ✅ Prevent double form submission
      $select.removeAttribute("name");
    }

    // buttons
    const btnGroup = document.createElement("div");
    btnGroup.className = "btn-group1 flex-wrap";
    btnGroup.role = "group";

    options.forEach((opt, i) => {
      const btnClass = opt.dataset.class ? "btn-" + opt.dataset.class : btncls;
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = `btn btn-sm ${btnClass} rounded-pill jselect-btn me-1 mb-1`;
      btn.innerHTML = opt.innerHTML.trim();
      btn.dataset.index = String(i);             // ✅ bind by index
      btn.dataset.value = opt.value;             // keep for convenience
      if (opt.dataset.other === "true") btn.dataset.isOther = "true";
      if (opt.selected) btn.classList.add("active");
      btnGroup.appendChild(btn);
    });

    // custom input
    const customInput = document.createElement("input");
    customInput.type = itype;
    customInput.className = "form-control form-control-sm mt-2 d-none jselect-custom";
    customInput.placeholder = "Please specify...";

    // mount
    $select.style.display = "none";
    $select.parentNode.insertBefore(wrapper, $select);
    wrapper.appendChild(btnGroup);
    wrapper.appendChild(customInput);
    wrapper.appendChild($select);

    // click handler
    btnGroup.addEventListener("click", (e) => {
      if (e.target.tagName !== "BUTTON") return;
      const btn = e.target;
      const idx = parseInt(btn.dataset.index, 10);
      const opt = options[idx];

      if (multiple) {
        // toggle selection
        opt.selected = !opt.selected;
      } else {
        options.forEach(o => (o.selected = false));
        opt.selected = true;
        wrapper.querySelector("input[type=hidden]").value = opt.value;
      }

      // show/hide custom
      if (btn.dataset.isOther === "true") {
        customInput.classList.remove("d-none");
        customInput.focus();
      } else {
        customInput.classList.add("d-none");
        customInput.value = "";
      }

      // fire change -> will trigger refreshButtons
      $select.dispatchEvent(new Event("change"));
    });

    // custom input updates "Other" option + its button
    customInput.addEventListener("input", () => {
      const val = customInput.value.trim();
      const otherIdx = options.findIndex(o => o.dataset.other === "true");
      if (otherIdx !== -1) {
        const otherOpt = options[otherIdx];
        otherOpt.value = val;
        otherOpt.textContent = val;
        otherOpt.selected = true;

        const otherBtn = btnGroup.querySelector(`.jselect-btn[data-index="${otherIdx}"]`);
        if (otherBtn) {
          otherBtn.dataset.value = val;       // ✅ keep button in sync
          otherBtn.innerHTML = val || "Other";
        }

        if (!multiple) wrapper.querySelector("input[type=hidden]").value = val;
        $select.dispatchEvent(new Event("change"));
      }
    });

    // keep UI in sync with select (works for .val(...).trigger('change'))
    function refreshButtons() {
      btnGroup.querySelectorAll(".jselect-btn").forEach(b => {
        const i = parseInt(b.dataset.index, 10);
        b.classList.toggle("active", !!options[i].selected);
      });

      const otherIdx = options.findIndex(o => o.dataset.other === "true");
      if (otherIdx !== -1) {
        if (options[otherIdx].selected) {
          customInput.classList.remove("d-none");
          customInput.value = options[otherIdx].value;
        } else {
          customInput.classList.add("d-none");
        }
      }
    }

    // Add after refreshButtons() inside initJSelect
    $select.addNewOptionButton = function (opt) {
      const btnClass = opt.dataset.class ? "btn-" + opt.dataset.class : btncls;
      const btn = document.createElement("button");
      btn.type = "button";
      btn.className = `btn btn-xs ${btnClass} rounded-pill jselect-btn me-1 mb-1`;
      btn.innerHTML = opt.innerHTML.trim();
      btn.dataset.value = opt.value;
      btn.dataset.index = String(options.length);
      options.push(opt);
      btnGroup.appendChild(btn);
      refreshButtons();
    };

    // $select.addEventListener("change", refreshButtons);
    $($select).on("change", refreshButtons); // jQuery event binding
    // initial paint
    refreshButtons();
  }

  // Rebuild jSelect after reloading options
  window.refreshJSelect = function (selectId) {
    const el = document.getElementById(selectId);
    if (!el) return;

    const wrapper = el.closest(".jselect-wrapper");
    if (wrapper) {
      // ✅ Move the select element out before removing the wrapper
      wrapper.parentNode.insertBefore(el, wrapper);
      wrapper.remove();
    }

    // Reset flag so it can be reinitialized
    el.dataset.jselectInitialized = "false";

    // Reinitialize
    initJSelect(el);
  };

  document.querySelectorAll("select.jselect").forEach(initJSelect);
});