// ===== CONTACT : envoi du formulaire sans recharger la page =====
const contactForm = document.getElementById("contact-form");

if (contactForm) {
	const formStatus = document.getElementById("form-status");
	const submitButton = contactForm.querySelector(".btn-submit");

	contactForm.addEventListener("submit", async (event) => {
		event.preventDefault(); // empêche le rechargement de la page

		submitButton.disabled = true;
		submitButton.textContent = "Sending...";
		formStatus.className = "form-status";

		try {
			const response = await fetch("contact.php", {
				method: "POST",
				body: new FormData(contactForm),
			});
			const result = await response.json();

			formStatus.textContent = result.message;
			formStatus.classList.add(result.success ? "success" : "error");

			if (result.success) {
				contactForm.reset();
			}
		} catch (error) {
			formStatus.textContent = "An error occurred. Please try again later.";
			formStatus.classList.add("error");
		} finally {
			submitButton.disabled = false;
			submitButton.textContent = "Send message";
		}
	});
}
