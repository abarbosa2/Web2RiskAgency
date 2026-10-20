document.addEventListener("DOMContentLoaded", () => {
	const header = document.querySelector("header");

	if (!header) {
		console.error(
			"Header introuvable : vérifie qu'il y a bien une balise <header> dans ton HTML.",
		);
		return;
	}

	function updateHeader() {
		const scroll =
			window.scrollY ||
			document.documentElement.scrollTop ||
			document.body.scrollTop;
		header.classList.toggle("scrolled", scroll > 50);
	}

	window.addEventListener("scroll", updateHeader);
	document.body.addEventListener("scroll", updateHeader);
	updateHeader();
});
