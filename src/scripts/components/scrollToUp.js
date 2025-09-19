const scrollToUp = () => {
  const scrollToUpButton = document.querySelector("#scrollUp");

  window.onscroll = () => {
    let scrollTop = window.scrollY;
    let docHeight = document.body.offsetHeight;
    let winHeight = window.innerHeight;
    let scrollPercent = scrollTop / (docHeight - winHeight);
    // let scrollPercentRounded = Math.round(scrollPercent * 100);
    let degrees = scrollPercent * 360;

    scrollToUpButton.style.display =
      document.body.scrollTop > 80 || document.documentElement.scrollTop > 80
        ? "flex"
        : "none";

    document.querySelector("#scrollUp").style.background =
      `conic-gradient(#FA9E0D ${degrees}deg, #FEE6C1 ${degrees}deg)`;
  };
};

export default scrollToUp;
