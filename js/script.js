/* Search form */
function toggleSearch() {
    document.querySelector(".search-form").classList.toggle("toggle-search-form");
}

/* Fix pagination */
if ((document.getElementsByClassName("next").length > 0) || (document.getElementsByClassName("prev").length > 0)) {
    const addCSS = (s) => ((d, e) => {
        e = d.createElement("style");
        e.innerHTML = s;
        d.head.appendChild(e)
    })(document);
    addCSS(".pagination li { padding-right: 10px; }")
}

/* Modal window */
function toggleModal() {
    document.querySelector(".modal").classList.toggle("toggle-modal");
}

/* Load image */
document.getElementById("files").onchange = function () {
    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById("image").src = e.target.result;
    };
    reader.readAsDataURL(this.files[0]);
};