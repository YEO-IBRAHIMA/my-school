window.copyPEVPostShortcode = id => {
	const inputEl = document.querySelector(`#pevPostShortcode-${id} input`);
	const tooltipEl = document.querySelector(`#pevPostShortcode-${id} span`);

	inputEl.select();
	inputEl.setSelectionRange(0, 30);
	document.execCommand('copy');

	tooltipEl.textContent = 'Copied Successfully!';
	setTimeout(() => {
		tooltipEl.textContent = 'Click to Copy';
	}, 1500);
}