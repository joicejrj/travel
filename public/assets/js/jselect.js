function jselectInit(selectList = document.querySelectorAll('select.jselect')) {
	selectList.forEach((select, index) => {
		select.classList.add("jselect-hidden");

		const isMultiple = select.hasAttribute("multiple");
		const selectedBg = select.dataset.color || "#0088cc";
		const hoverBg = select.dataset.hover || "#f5f5f5";
		const textColor = select.dataset.textcolor || "#ffffff";
		const hoverText = select.dataset.hovertext || "#000000";

		const wrapper = document.createElement("div");
		wrapper.className = "jselect-wrapper";
		const wrapperId = select.id || "jselect-" + index;
		wrapper.dataset.selectId = wrapperId;

		// Inject dynamic CSS
		const styleId = `jselect-style-${wrapperId}`;
		if (!document.getElementById(styleId)) {
			const style = document.createElement("style");
			style.id = styleId;
			style.innerHTML = `
				.jselect-wrapper[data-select-id="${wrapperId}"] .jselect-button:not(.jselect-selected):hover:not(.jselect-disabled) {
					background: ${hoverBg};
					color: ${hoverText};
				}
				.jselect-wrapper[data-select-id="${wrapperId}"] .jselect-selected {
					background: ${selectedBg};
					color: ${textColor};
					border-color: ${selectedBg};
				}
			`;
			document.head.appendChild(style);
		}

		const anySelected = Array.from(select.options).some(o => o.selected);

		// Build buttons
		Array.from(select.options).forEach(option => {
			if (option.value === "") return; // skip empty option

			const label = document.createElement("label");
			label.className = "jselect-button";
			label.textContent = option.textContent;
			label.dataset.value = option.value;

			if (option.disabled) label.classList.add("jselect-disabled");
			if (anySelected && option.selected) {
				label.classList.add("jselect-selected");
			}

			label.addEventListener("click", () => {
				if (option.disabled) return;

				if (isMultiple) {
					label.classList.toggle("jselect-selected");
					option.selected = !option.selected;

					// Fire change event
					select.dispatchEvent(new Event("change", { bubbles: true }));
				} else {
					const wasSelected = label.classList.contains("jselect-selected");
					wrapper.querySelectorAll(".jselect-button").forEach(l => l.classList.remove("jselect-selected"));
					Array.from(select.options).forEach(o => o.selected = false);

					if (!wasSelected) {
						label.classList.add("jselect-selected");
						option.selected = true;
					} else {
						const blank = select.querySelector('option[value=""]');
						if (blank) blank.selected = true;
					}

					// Fire the change event
					select.dispatchEvent(new Event("change", { bubbles: true }));
				}
			});

			wrapper.appendChild(label);
		});

		select.parentNode.insertBefore(wrapper, select.nextSibling);
	});
}

// ✅ Initialize on page load
document.addEventListener("DOMContentLoaded", function () {
	jselectInit();
});

// ✅ Refresh function to rebind after changing options
function refreshJselect(selectId) {
	const selectElement = document.getElementById(selectId);
	
	// Remove existing wrapper
	const wrapper = selectElement.nextElementSibling;
	if (wrapper && wrapper.classList.contains('jselect-wrapper')) {
		wrapper.remove();
	}

	// Remove generated style (optional cleanup)
	const wrapperId = selectElement.id || "jselect";
	const style = document.getElementById(`jselect-style-${wrapperId}`);
	if (style) style.remove();

	// Re-init
	selectElement.classList.remove('jselect-hidden');
	jselectInit([selectElement]);
}