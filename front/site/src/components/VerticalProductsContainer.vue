<template>
  <div class="vertical-products-container">
    <Box>
      <div class="d-flex justify-content-between">
        <div class="title">{{ title }}</div>
        <div class="arrows">
          <i class="inactive fas fa-arrow-alt-left"></i>
          <i class="fas fa-arrow-alt-right"></i>
        </div>
      </div>
    </Box>

    <div class="mt-3 d-none d-md-block">
      <div class="product mt-3" v-for="i in 5" :key="i">
        <div class="product-badge" :style="{ background: badgeColor }">
          233
          <sup>RUB</sup>
        </div>
        <div class="d-flex justify-content-between align-items-center">
          <img src="../assets/img/card-small.png" alt />
          <div class="pl-2">
            <span class="name">Biomutant - Xbox One - Key UNITED STATES</span>
            <span class="info">
              <i class="fas fa-user-tie"></i> VladimirDesigner
            </span>
            <span class="info">
              <i class="fas fa-cart-arrow-down"></i> 567
            </span>
          </div>
        </div>
      </div>
    </div>
    <div class="d-block d-md-none">
      <OwlCarousel :items="1" :nav="false" :dots="false">
        <div class="product mt-3" v-for="i in 5" :key="i">
          <div class="product-badge" :style="{ background: badgeColor }">
            233
            <sup>RUB</sup>
          </div>
          <div class="d-flex justify-content-between align-items-center">
            <img
              src="../assets/img/card-small.png"
              class="carousel-image"
              alt
            />
            <div class="pl-2">
              <span class="name">Biomutant - Xbox One - Key UNITED STATES</span>
              <span class="info">
                <i class="fas fa-user-tie"></i> VladimirDesigner
              </span>
              <span class="info">
                <i class="fas fa-cart-arrow-down"></i> 567
              </span>
            </div>
          </div>
        </div>
      </OwlCarousel>
    </div>
  </div>
</template>

<script>
import Box from '@/components/Box';
import OwlCarousel from 'vue-owl-carousel';

export default {
  components: {
    Box,
    OwlCarousel
  },
  props: {
    title: String,
    badgeColor: {
      type: String,
      default: '#2b7bfe'
    }
  }
};
</script>

<style lang="scss" scoped>
.title {
  position: relative;
  margin: 0;
  padding: 0 0 0 17px;
  font-weight: 500;

  &:before {
    content: '';
    display: block;
    width: 7px;
    height: 7px;
    border: 2px solid #2b7bfe;
    border-radius: 50%;
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
  }
}

.arrows {
  i {
    margin: 0 10px;
    cursor: pointer;
    font-size: 20px;
    color: #1c1c1c;
  }

  .inactive {
    color: rgba(#1c1c1c, 0.35);
  }
}

.product {
  position: relative;
  border-radius: 8px;
  background: #fff;
  padding: 20px 24px;

  img {
    width: 122px;
    max-width: 122px;
    height: 70px;
  }

  @media screen and (max-width: 600px) {
    img {
      width: 100px;
    }
  }

  &-badge {
    padding: 10px;
    border-radius: 8px 8px 8px 0px;
    background: #2b7bfe;

    position: absolute;
    left: 0;
    top: 0;
    color: #fff;
    font-weight: 500;
  }

  &-badge_green {
    background: #35d48a;
  }

  .name {
    font-size: 13px;
    display: block;
  }

  .info {
    padding: 5px 0 0;
    display: block;
    color: rgba(#1c1c1c, 0.5);
    i {
      font-size: 13px;
      color: #2b7bfe;
    }
  }
}
</style>
