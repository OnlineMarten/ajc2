<template>
<div>

    <div class="row">
        <div class="col-md-12">

            <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
                <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {{ message }}
            </div>

            <div class="card" >
                <div class="card-header" style="height: 120px; background-color: rgba(255,0,0,0.1);">
                    <h3>Baskets</h3>
                   <p>(automatically refreshes every 10 seconds)</p>
                    <h5>{{ refresh }}</h5>

                </div>
                <!--card header-->
                <div class="card-body" >


                    <br><hr>
            <!--        <input type="checkbox" id="checkbox" v-model="show_details_table">
                    <label for="checkbox">show detailed table</label>
                    <input type="checkbox" id="checkbox" v-model="show_past_events">
                    <label for="checkbox">show past events</label>
            -->
                    <table class="table table-striped table-bordered table-responsive table-sm" v-if="baskets.length > 0" ref="table">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Idle time (minutes)</th>
                                <th>Last updated</th>
                                <th>Created</th>
                                <th>Ticket nr</th>
                                <th>Event Date</th>
                                <th>Tickets</th>
                                <th>Name</th>
                                <th>Country</th>
                                <th>Promocode</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(basket, index) in baskets" :key="basket.id">
                         <!--   <template v-if="show_past_sales || (!show_past_sales && (new Date(sale.event_date) >= Date.now()))">
-->
                                <td>
                                    {{ basket.id}}
                                </td>
                                <td>
                                    {{ Math.floor((Date.now()- new Date(basket.updated_at))/(1000*60)) }} min.
                                </td>
                                <td>
                                    {{ new Date(basket.updated_at) | dateFormat('dd DD MMM HH:mm ') }}
                                </td>
                                <td>
                                    {{ new Date(basket.created_at) | dateFormat('dd DD MMM HH:mm ') }}
                                </td>
                                 <td>
                                    {{ basket.ticket_nr}}
                                </td>
                                <td>
                                    {{ new Date(basket.event_date) | dateFormat('dd DD MMM YYYY') }}
                                </td>
                                <td>
                                    {{ basket.nr_tickets}}  {{basket.ticket_title}}
                                </td>
                                <td>
                                    {{ basket.name}}
                                </td>
                                <td>
                                    {{ basket.country_code}}
                                </td>
                                <td>
                                       <span v-if="basket.promocode">{{ basket.promocode}}</span>
                                </td>
                                <td>
                                    {{ basket.status}}
                                </td>
                                <td>
                                    <button @click="deleteBasket(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>

                        <!--    </template>-->
                              </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>No baskets</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->




</div>
</template>



<script>
import { VueTelInput } from 'vue-tel-input'

//import axioscalls from '@./resources/services/axioscalls'
export default {
  components: {
        VueTelInput,
  },

  data(){
    return {
        refresh:"",
        counter:"0",
        show_error:false,
        show: false,
        error:"",
        errors: "",
        message: "",
        baskets:{},
    }//return
  },//data


    mounted() {
        console.log('mounted');
        this.readBaskets();
        //load promocodes and available events so it is ready in case we want to add or update a sale.

         setInterval(function () {
             setTimeout(() => {
            this.refresh = "";
            }, 500);
            this.refresh = "Refreshing baskets";
            this.readBaskets();
            // this.refresh = "";
            }.bind(this), 10000);

    },//mounted

    methods: {

        readBaskets(){
            axios.get("/admin/basket").then(response => {
                this.baskets = response.data.baskets;

            });
        },


        readPromoCodes(){
            axios.get("admin/promocode").then(response => {
                this.promocodes = response.data.promocodes;
            });
        },



        deleteBasket(index) {
        let conf = confirm(
        'Do you ready want to delete this basket "' +
          this.baskets[index].updated_at +
          '"?'
        );
        if (conf === true) {
            axios
            .delete("/admin/basket/" + this.baskets[index].id)

            .then(response => {
                this.baskets.splice(index, 1);
                this.showMessage(response.data.message);
            })

            .catch(error => {
                this.showMessage(error.response.data.message);
                // Error
            });
        }
        },

        //helper function for formatting ticketnr
        formatDate(date, no_year="") {
            var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear().toString().substr(-2);
            if (month.length < 2)
                month = '0' + month;
            if (day.length < 2)
                day = '0' + day;
            if (no_year) return [day,month].join('');
            else return [day,month,year].join('');
        },

        showMessage(message) {
            this.message = message;
            this.show = true;
        },

         showErrors(error) {
            this.errors = "<ul>";
            let response = error.response;
            Object.keys(response.data.errors).forEach(item => {
                this.errors += "<li>" + response.data.errors[item] + "</li>";
            });
            this.errors += "</ul>";
        },




    },//methods
}//export default
</script>
