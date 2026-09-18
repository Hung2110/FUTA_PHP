function openModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "flex";
    }
}

function closeModal(id) {
    const modal = document.getElementById(id);
    if (modal) {
        modal.style.display = "none";
    }
}

function openApplyModal(jobTitle) {
    const titleEl = document.getElementById("applyJobTitle");
    const posEl = document.getElementById("applicationPosition");
    if (titleEl) {
        titleEl.innerText = "Công việc: " + jobTitle;
    }
    if (posEl) {
        posEl.value = jobTitle;
    }
    openModal("applyModal");
}

