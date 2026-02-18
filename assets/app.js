const navToggle = document.getElementById("navToggle");
const siteNav = document.getElementById("siteNav");
const yearEl = document.getElementById("year");
const form = document.getElementById("contactForm");
const formNote = document.getElementById("formNote");

if (yearEl) {
  yearEl.textContent = new Date().getFullYear();
}

if (navToggle && siteNav) {
  navToggle.addEventListener("click", () => {
    siteNav.classList.toggle("open");
  });
}

if (form && formNote) {
  form.addEventListener("submit", (event) => {
    event.preventDefault();
    if (!form.checkValidity()) {
      formNote.textContent = "Please complete all required fields.";
      return;
    }

    formNote.textContent = "Thanks. Your message is ready to be sent.";
    form.reset();
  });
}
