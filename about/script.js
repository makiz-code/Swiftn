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

const departureDateInput = document.getElementById('departure_date');
const returnDateInput = document.getElementById('return_date');

departureDateInput.addEventListener('change', () => {
  const selectedDate = new Date(departureDateInput.value);
  const nextDay = new Date(selectedDate.getTime() + 24 * 60 * 60 * 1000);
  const formattedNextDay = nextDay.toISOString().split('T')[0];
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

function wait(ms) {
  return new Promise((resolve) => {
    setTimeout(resolve, ms);
  });
}

async function translate(element) {
  while (true) {
    element.classList.add("translate");
    await wait(2500);
    element.classList.remove("translate");
    await wait(500);
  }
}
const icons = document.querySelectorAll(".about-content .content .icons i");
icons.forEach((element) => {
  translate(element);
});
