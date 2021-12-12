$(document).ready(function () {});

var vsOpts = {
  $slides: $(".v-slide"),
  $list: $(".v-slides"),
  duration: 1,
  lineHeight: 20,
};

var vSlide = new TimelineMax({
  paused: true,
  delay: 8,
  repeat: -1,
});

vsOpts.$slides.each(function (i) {
  vSlide.to(vsOpts.$list, vsOpts.duration, {
    y: i * -1 * vsOpts.lineHeight,
    ease: Sine.easeInOut,
  });
});
vSlide.play();
