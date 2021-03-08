<template>
  <div class="orderPage">
    <page-tab :tabs="[{hash: 'info', name: 'Информация'},{hash: 'messaging', name: 'Переписка'},{hash: 'review', name: 'Отзыв'},]">
      <template v-slot:default="slotProps">
        <v-tab-item value="info">
          <info-tab v-if="slotProps.tab==='info'"/>
        </v-tab-item>
        <v-tab-item value="messaging">
          <messaging-tab v-if="slotProps.tab==='messaging'"/>
        </v-tab-item>
        <v-tab-item value="review">
          <review-tab v-if="slotProps.tab==='review'"/>
        </v-tab-item>
      </template>
    </page-tab>
  </div>
</template>

<script>
import {page} from './../../mixin/page';
import PageTab from "../../component/PageTab";
import InfoTab from "../../component/order/InfoTab";
import MessagingTab from "../../component/order/MessagingTab";
import ReviewTab from "../../component/order/ReviewTab";

export default {
  mixins: [page],
  components: {PageTab, InfoTab, MessagingTab, ReviewTab},
}
</script>
