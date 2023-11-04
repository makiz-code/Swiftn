class Swiper {
  constructor(element) {
    this.element = element;
    this.wrapper = this.element.querySelector(".swiper-container");
    this.slides = this.element.querySelectorAll(".swiper-slide");
    this.activeIndex = 0;
    this.setActiveSlide();

    this.nextBtn = this.element.querySelector(".swiper-button-next");
    this.prevBtn = this.element.querySelector(".swiper-button-prev");

    this.nextBtn.addEventListener("click", () => {
      this.next();
    });

    this.prevBtn.addEventListener("click", () => {
      this.prev();
    });

    setInterval(() => {
      if (this.activeIndex === this.slides.length - 1) {
        this.direction = "prev";
      } else if (this.activeIndex === 0) {
        this.direction = "next";
      }
      if (this.direction === "next") {
        this.next();
      } else {
        this.prev();
      }
    }, 3000);
  }

  setActiveSlide() {
    this.slides.forEach((slide, index) => {
      if (index === this.activeIndex) {
        slide.style.backgroundImage = `url(${slide.dataset.bg})`;
        slide.classList.add("active");
        slide.classList.remove("prev", "next");
      } else if (index < this.activeIndex) {
        slide.style.backgroundImage = `url(${slide.dataset.bg})`;
        slide.classList.add("prev");
        slide.classList.remove("active", "next");
      } else {
        slide.style.backgroundImage = `url(${slide.dataset.bg})`;
        slide.classList.add("next");
        slide.classList.remove("active", "prev");
      }
    });
  }

  next() {
    if (this.activeIndex < this.slides.length - 1) {
      this.activeIndex++;
      this.setActiveSlide();
    }
    if (this.activeIndex === this.slides.length - 1) {
      this.nextBtn.setAttribute("disabled", "disabled");
    }
    if (this.activeIndex > 0) {
      this.prevBtn.removeAttribute("disabled");
    }
  }

  prev() {
    if (this.activeIndex > 0) {
      this.activeIndex--;
      this.setActiveSlide();
    }
    if (this.activeIndex === 0) {
      this.prevBtn.setAttribute("disabled", "disabled");
    }
    if (this.activeIndex < this.slides.length - 1) {
      this.nextBtn.removeAttribute("disabled");
    }
  }
}

const swiperElement = document.querySelector(".swiper-container");
const swiper = new Swiper(swiperElement);

let signupBtn = document.getElementById("signup-btn");
let loginBtn = document.getElementById("login-btn");
let logoutBtn = document.getElementById("logout-btn");
let menu = document.querySelector("#menu-btn");
let navbar = document.querySelector(".header .navbar");

loginBtn.addEventListener("click", () => {
  window.location.href = `../authentification/index.php?link=${
    window.location.origin + window.location.pathname
  }`;
});
logoutBtn.addEventListener("click", () => {
  window.location.href = "./index.php?logout=1";
});

signupBtn.addEventListener("click", () => {
  window.location.href = `../authentification/index.php?register=true&link=${
    window.location.origin + window.location.pathname
  }`;
});

menu.onclick = () => {
  menu.classList.toggle("fa-times");
  navbar.classList.toggle("active");
};

let searchForm = document.querySelector(".search");
document.querySelector("#search-btn").onclick = () => {
  searchForm.classList.add("active");
};
document.querySelector("#close-btn").onclick = () => {
  searchForm.classList.remove("active");
};

const departureDateInput = document.getElementById("departure_date");
const returnDateInput = document.getElementById("return_date");

departureDateInput.addEventListener("change", () => {
  const selectedDate = new Date(departureDateInput.value);
  const nextDay = new Date(selectedDate.getTime() + 24 * 60 * 60 * 1000);
  const formattedNextDay = nextDay.toISOString().split("T")[0];
  returnDateInput.value = formattedNextDay;
  returnDateInput.min = returnDateInput.value;
});

const orders = document.querySelectorAll(".submit-search .order");
const hiddenInput = document.getElementById("order");

orders.forEach((order) => {
  order.addEventListener("click", () => {
    orders.forEach((otherOrder) => {
      if (order !== otherOrder) {
        otherOrder.classList.remove("active");
      }
    });
    order.classList.add("active");
    hiddenInput.value = order.classList.contains("up") ? "ASC" : "DESC";
    console.log(hiddenInput.value);
  });
});

let cart = document.querySelector(".cart");

document.querySelector("#cart-btn").onclick = () => {
  cart.classList.add("active");
};
window.onclick = (e) => {
  if (e.target == searchForm) {
    searchForm.classList.remove("active");
  } else if (e.target == cart) {
    cart.classList.remove("active");
  } else if (e.target == navbar) {
    menu.classList.remove("fa-times");
    navbar.classList.remove("active");
  }
};

let plus = [...document.querySelectorAll(".fa-plus")];
let minus = [...document.querySelectorAll(".fa-minus")];
let nbr = [...document.querySelectorAll(".nbr")];
let price = [...document.querySelectorAll(".product .price")];
let sub_Total = document.querySelector(".sub-total");
let discount = document.querySelector(".discount");
let total = document.querySelector(".total");

function calcul() {
  let count = 0;
  let nbrs = 0;
  for (let i = 0; i < price.length; i++) {
    count += parseInt(price[i].textContent) * parseInt(nbr[i].textContent);
    nbrs += parseInt(nbr[i].textContent);
  }
  sub_Total.innerHTML = count;
  nbrs *= 50;
  discount.innerHTML = nbrs;
  total.innerHTML = count - nbrs;
}
calcul();

for (let i = 0; i < plus.length; i++) {
  plus[i].addEventListener("click", () => {
    calcul();
  });
  minus[i].addEventListener("click", () => {
    calcul();
  });
}

let products = document.querySelector(".cart .products");
let prices = document.querySelector(".cart .prices");
let flip = document.querySelector(".fa-rotate");

if (flip.dataset.count == 1) {
  flip.classList.add("show");
  flip.addEventListener("click", () => {
    if (products.classList.contains("inactif")) {
      products.classList.add("actif");
      flip.style.rotate = "-360deg";
    } else {
      products.classList.remove("actif");
      flip.style.rotate = "360deg";
    }
    products.classList.toggle("inactif");
    prices.classList.toggle("inactif");
    prices.classList.toggle("actif");
  });
}

const left_btn = document.querySelector(".featured-destinations .prev");
const right_btn = document.querySelector(".featured-destinations .next");

let currentPosition = 0;
let items = document.querySelectorAll(".destination-card");
let itemWidth = items[0].offsetWidth;
let itemMargin = window.getComputedStyle(items[0]).margin;
itemMargin = parseInt(itemMargin, 10);
let containerWidth = document.querySelector(
  ".destinations-container"
).offsetWidth;
let itemsPerPage = Math.floor(containerWidth / itemWidth);
let maxMove = items.length - itemsPerPage;

const moveItems = (increment) => {
  currentPosition += increment;
  if (currentPosition > maxMove) currentPosition = maxMove;
  if (currentPosition < 0) currentPosition = 0;
  items.forEach((item) => {
    item.style.transform = `translateX(-${
      currentPosition * (itemWidth + itemMargin * 2)
    }px)`;
  });
  if (currentPosition >= maxMove) {
    right_btn.disabled = true;
  } else {
    right_btn.disabled = false;
  }
  if (currentPosition <= 0) {
    left_btn.disabled = true;
  } else {
    left_btn.disabled = false;
  }
};

function executeUpdates() {
  items = document.querySelectorAll(".destination-card");
  itemWidth = items[0].offsetWidth;
  itemMargin = window.getComputedStyle(items[0]).margin;
  itemMargin = parseInt(itemMargin, 10);
  containerWidth = document.querySelector(
    ".destinations-container"
  ).offsetWidth;
  itemsPerPage = Math.floor(containerWidth / itemWidth);
  maxMove = items.length - itemsPerPage;
}

function updateItemsWidth() {
  let mob_view = window.matchMedia("(max-width: 100vw)");
  let mob_view2 = window.matchMedia("(max-width: 1200px)");
  let mob_view3 = window.matchMedia("(max-width: 768px)");
  if (mob_view.matches) {
    items.forEach((item) => {
      item.style.minWidth = "30%";
      item.style.margin = "1.666%";
    });
  }
  if (mob_view2.matches) {
    items.forEach((item) => {
      item.style.minWidth = "45%";
      item.style.margin = "2.4%";
    });
  }
  if (mob_view3.matches) {
    items.forEach((item) => {
      item.style.minWidth = "90%";
      item.style.margin = "4.7%";
    });
  }
  moveItems(0);
  currentPosition = 0;
  executeUpdates();
}

window.addEventListener("load", updateItemsWidth);
window.addEventListener("resize", updateItemsWidth);

// recalculate itemWidth and itemMargin after transition ends
items[0].addEventListener("transitionend", executeUpdates);

right_btn.addEventListener("click", () => moveItems(1));
left_btn.addEventListener("click", () => moveItems(-1));

document.addEventListener("keydown", (event) => {
  if (event.code === "ArrowRight") {
    moveItems(1);
  } else if (event.code === "ArrowLeft") {
    moveItems(-1);
  }
});

const scrollToTopBtn = document.querySelector("#scroll-top-btn");

// show the button when user scrolls
window.addEventListener("scroll", function () {
  if (window.pageYOffset > 0) {
    scrollToTopBtn.classList.add("visible");
  } else {
    scrollToTopBtn.classList.remove("visible");
  }
});

// hide the button after 5 seconds of inactivity
let timeoutId;
window.addEventListener("scroll", function () {
  clearTimeout(timeoutId);
  timeoutId = setTimeout(function () {
    scrollToTopBtn.classList.remove("visible");
  }, 2000);
});

// scroll to top when the button is clicked
scrollToTopBtn.addEventListener("click", function () {
  window.scrollTo({
    top: 0,
    behavior: "smooth",
  });
});

let loadMoreBtn = document.querySelector(".offers .load-more");
let offerCard = [...document.querySelectorAll(".offer-card")];
for (let i = 0; i < 3; i++) {
  offerCard[i].classList.add("show");
}

let currentitem = 3;
loadMoreBtn.onclick = () => {
  let offerCard = [...document.querySelectorAll(".offer-card")];
  if (loadMoreBtn.innerHTML != "View All") {
    for (let i = currentitem; i < currentitem + 3; i++) {
      if (offerCard[i]) {
        offerCard[i].classList.add("show");
      }
    }
    currentitem += 3;
    if (currentitem >= offerCard.length) {
      loadMoreBtn.innerHTML = "View All";
      loadMoreBtn.firstChild.style.color = "var(--white)";
    }
  } else {
    window.location.href = "../offers/index.php";
  }
};

//animations functions
function wait(ms) {
  return new Promise((resolve) => {
    setTimeout(resolve, ms);
  });
}
async function rotate() {
  const button = document.querySelector(".view-all button");
  while (true) {
    button.classList.add("rotate");
    await wait(1000);
    button.classList.remove("rotate");
    await wait(1000);
  }
}
async function scale() {
  while (true) {
    loadMoreBtn.classList.add("scale");
    await wait(1000);
    loadMoreBtn.classList.remove("scale");
    await wait(1000);
  }
}

async function color(element, delay) {
  await wait(delay);
  element.classList.add("color");
  await wait(5000);
  element.classList.remove("color");
}

async function runColors() {
  while (true) {
    let i = 0;
    const icons = [...document.querySelectorAll(".service i")];
    for (i = 0; i < icons.length; i++) {
      color(icons[i], i * 1000);
    }
    await wait(5000 + icons.length * 1000);
  }
}

runColors();
rotate();
scale();
