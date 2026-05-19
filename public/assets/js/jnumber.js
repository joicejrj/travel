/*!
 * jnumber.js
 * Bootstrap-friendly number input with + / - buttons
 * Auto-detects dynamically added/removed inputs
 */

(function () {
  const INIT_ATTR = 'data-jnumber-init';

  /* ---------- Inject CSS automatically ---------- */
  function injectCSS() {
    if (document.getElementById('jnumber-styles')) return;

    const style = document.createElement('style');
    style.id = 'jnumber-styles';
    style.innerHTML = `
      .jnumber-wrapper input {
        text-align: center;
      }
    `;
    document.head.appendChild(style);
  }

  function createJNumber(input) {
    if (input.hasAttribute(INIT_ATTR)) return;
    if (input.type !== 'number') return;

    input.setAttribute(INIT_ATTR, '1');

    const step = parseFloat(input.step) || 1;
    const min = input.min !== '' ? parseFloat(input.min) : null;
    const max = input.max !== '' ? parseFloat(input.max) : null;

    /* Wrapper */
    const wrapper = document.createElement('div');
    wrapper.className = 'input-group jnumber-wrapper';

    /* Minus button */
    const minusBtn = document.createElement('button');
    minusBtn.type = 'button';
    minusBtn.className = 'btn btn-outline-secondary';
    minusBtn.innerHTML = '−';

    /* Plus button */
    const plusBtn = document.createElement('button');
    plusBtn.type = 'button';
    plusBtn.className = 'btn btn-outline-secondary';
    plusBtn.innerHTML = '+';

    /* Insert wrapper */
    input.parentNode.insertBefore(wrapper, input);
    wrapper.appendChild(minusBtn);
    wrapper.appendChild(input);
    wrapper.appendChild(plusBtn);

    function clamp(value) {
      if (min !== null && value < min) value = min;
      if (max !== null && value > max) value = max;
      return value;
    }

    minusBtn.addEventListener('click', () => {
      let val = parseFloat(input.value) || 0;
      input.value = clamp(val - step);
      input.dispatchEvent(new Event('change', { bubbles: true }));
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });

    plusBtn.addEventListener('click', () => {
      let val = parseFloat(input.value) || 0;
      input.value = clamp(val + step);
      input.dispatchEvent(new Event('change', { bubbles: true }));
      input.dispatchEvent(new Event('input', { bubbles: true }));
    });
  }

  function scan() {
    document.querySelectorAll('input.jnumber').forEach(createJNumber);
  }

  /* Observe DOM changes */
  const observer = new MutationObserver(() => scan());

  observer.observe(document.body, {
    childList: true,
    subtree: true
  });

  /* Init */
  document.addEventListener('DOMContentLoaded', () => {
    injectCSS();
    scan();
  });
})();