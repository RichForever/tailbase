// eslint-disable-next-line import/no-named-as-default
import Alpine from "alpinejs";
window.Alpine = Alpine;
Alpine.start();

import anchorScroll from "./components/anchorScroll";
import scrollToUp from "./components/scrollToUp";
import { initBlock } from "./blocks";

const blockNames = ["sample-block-1"];

for (const item of blockNames) {
  // eslint-disable-next-line no-unused-vars
  initBlock(item).then((blocks) => {});
}

window.addEventListener("DOMContentLoaded", () => {
  anchorScroll();
  scrollToUp();
});
