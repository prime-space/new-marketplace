<template>
  <div class="carousel">
    <div class="navigation">
      <div
        class="dot"
        v-for="(image, i) in images"
        :key="i"
        @click="changeSlide(i)"
        :class="{ active: currentActive === i }"
      ></div>
    </div>
    <div class="images">
      <div
        v-for="(image, i) in images"
        :style="{ backgroundImage: `url(${image.url})` }"
        :key="i"
        :class="{ active: currentActive === i }"
      ></div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    images: {
      type: Array
    }
  },
  mounted() {
    setInterval(() => {
      this.changeSlide(this.currentActive + 1);
    }, 4000);
  },
  data() {
    return {
      currentActive: 0
    };
  },

  methods: {
    changeSlide(i) {
      if (i >= this.images.length) {
        this.currentActive = 0;
        return;
      }

      this.currentActive = i;
    }
  }
};
</script>

<style lang="scss" scoped>
.carousel {
  width: 100%;
  height: 100%;
  position: relative;
  overflow: hidden;
  border-radius: 8px;

  height: 320px;

  @media screen and (max-width: 600px) {
    height: 230px;
  }

  &:before {
    content: '';
    border-radius: 8px;
    background: linear-gradient(0deg, rgba(0, 0, 0, 0) 0%, #000 100%);
    opacity: 0.7;

    height: 30%;

    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    z-index: 2;
  }

  position: relative;
  .navigation {
    position: absolute;
    z-index: 3;
    width: 100%;
    right: 0;
    top: 15px;
    display: flex;
    justify-content: flex-end;
    align-items: center;

    .dot {
      width: 20px;
      height: 20px;
      background: #fff;
      border-radius: 50%;
      cursor: pointer;
      margin: 0 10px;
      opacity: 0.5;
      transition: 0.5s;

      &:hover {
        opacity: 1;
      }
    }

    .active {
      opacity: 1;
    }
  }

  .images {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;

    div {
      position: absolute;
      width: 100%;
      background: no-repeat center center;
      background-size: cover;

      height: 100%;
      left: -100%;
      top: 0;
      transition: 0.3s;

      @media screen and (max-width: 500px) {
        background-size: 100% 100%;
      }
    }

    .active {
      left: 0;
    }
  }
}
</style>
