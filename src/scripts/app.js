import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

import anchorScroll from "./components/anchorScroll";
import scrollToUp from "./components/scrollToUp";

window.addEventListener("DOMContentLoaded", () => {
  anchorScroll();
  scrollToUp();
});

// Emit every file from src/images to build/images (webpack only outputs assets that are imported).
require.context("../images", true, /\.(jpe?g|png|gif|svg|webp|avif)$/i);
