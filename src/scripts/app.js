// eslint-disable-next-line import/no-named-as-default
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

import anchorScroll from "./components/anchorScroll";
import scrollToUp from "./components/scrollToUp";

window.addEventListener("DOMContentLoaded", () => {
  anchorScroll();
  scrollToUp();
});
