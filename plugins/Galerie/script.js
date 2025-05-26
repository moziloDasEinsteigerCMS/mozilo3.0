document.addEventListener("DOMContentLoaded", function () {
  const images = document.querySelectorAll(".gallerytd img");
  const modal = document.querySelector(".gallery-modal");
  const modalImg = document.querySelector(".modal-img");
  const modalTxt = document.querySelector(".modal-txt");
  const closebtn = document.querySelector(".gallery-modal .close");
  let currentIndex = 0;

  const prevBtn = document.querySelector(".gallery-modal .prev");
  const nextBtn = document.querySelector(".gallery-modal .next");

  //Add Click Event for All Images
  images.forEach((image, index) => {
    image.addEventListener("click", function () {
      currentIndex = index;
      updateModalContent();
      modal.classList.add("show");
document.body.style.overflow = 'hidden';
    });
  });

  //Update Image in Modal
  function updateModalContent() {
    const image = images[currentIndex];
    modalImg.classList.remove("show");
    setTimeout(() => {
      modalImg.src = image.src;
      if (!(modalTxt === null || typeof modalTxt === 'undefined')) {
modalTxt.innerHTML = image.alt;
}        
      modalImg.classList.add("show");
    }, 300);
  }

  //Next button onclick
  if (nextBtn) {  
  nextBtn.addEventListener("click", function () {
    currentIndex = currentIndex + 1 >= images.length ? 0 : currentIndex + 1;
    updateModalContent();
  });
  }

  //Previous button onclick
  if (prevBtn) {
  prevBtn.addEventListener("click", function () {
    currentIndex = currentIndex - 1 < 0 ? images.length - 1 : currentIndex - 1;
    updateModalContent();
  });}

  //Code for Close Icon
  if (closebtn) {
  closebtn.addEventListener("click", function () {
    modal.classList.remove("show");
    document.body.style.overflow = '';
  });
  }
});

// When the user clicks anywhere outside of the modal, close it
// Get the modal
var modal = document.querySelector(".gallery-modal");
window.onclick = function(event) {
  if (event.target == modal) {
    modal.classList.remove("show");
document.body.style.overflow = '';
  }
}
