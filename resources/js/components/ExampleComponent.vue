<template>
<div>

    <CrudSale v-bind:sale_id="sale_id" v-bind:event_id="event_id" v-on:returnValue="onCloseCrud"></CrudSale>
</div>

</template>

<script>
    import CrudSale from './CrudSaleComponent.vue'

export default {
  components: {
    CrudSale
  },
  data(){
    return {
        event_id:"33",
        sale_id:"",
    }//return
  },//data


    mounted() {
        console.log('mounted');




    },//mounted

    methods: {
        onCloseCrud: function (returnValue) {
            console.log('crud closed with value:'+returnValue);
        },
    }
}
</script>

