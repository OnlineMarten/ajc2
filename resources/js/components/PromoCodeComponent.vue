<template>
<div class="component-holder">
    <div class="row">
        <div class="col-md-12">

            <div class="alert alert-info alert-dismissible fade show" v-if="show" role="alert" id="alert" name="alert">
                <button type="button" class="close" v-on:click="show = !show" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                {{ message }}
            </div>

            <div class="card">
                <div class="card-header">Promocodes


                </div>
                <!--card header-->
                <div class="card-body">
                    <button @click="initAddPromoCode()" class="btn btn-primary btn-xs float-left">
                            + Add New PromoCode
                    </button>
                    <br><hr>
                    <table class="table table-striped table-responsive table-sm" v-if="promocodes.length > 0" ref="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Description</th>
                                <th>Discount</th>
                                <th>Apply to:<br>Tickets/Extras</th>
                                <th>Valid until</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            <tr v-for="(promocode, index) in promocodes" :key="promocode.id">

                                <td>
                                    {{ promocode.code }}
                                </td>
                                <td>
                                    {{ promocode.description }}
                                </td>
                                <td>
                                    <span  v-if="promocode.discount_amount"> {{ promocode.discount_amount|toCurrency  }}</span>
                                    <span v-else>{{ promocode.discount_perc }} %</span>
                                </td>
                                <td>
                                     <span v-if="promocode.apply_to_tickets">yes</span><span v-else>no</span> /
                                     <span v-if="promocode.apply_to_extras">yes</span><span v-else>no</span>
                                <td>
                                     {{ promocode.valid_until }}
                                </td>
                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deletePromoCode(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>no promocodes yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->

    <div class="modal fade" tabindex="-1" role="dialog" id="add_promocode_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <span v-if="add_update=='add'">Add New PromoCode</span><span v-else>Edit PromoCode</span>
                    </h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>

                </div>
                <!--modal header-->


                <div class="modal-body">

                    <div class="alert alert-danger" v-if="errors.length > 0">
                        We have errors:
                        <ul>
                            <span v-html="errors">{{ errors }}</span>
                        </ul>
                    </div>

                         <div class="form-group row">
                            <label for="name" class="col-sm-3 col-form-label">Code:</label>
                            <div class="col-sm-8 p-2  shadow-sm bg-light rounded">
                            <input required type="text" name="code" id="code" class="form-control"
                                v-model="promocode.code">
                            </div>
                        </div>

                        <div class="form-group row">
                        <label class="col-sm-3 col-form-label">Valid until:</label>
                            <div class="col-sm-8">
                               <input required type="date" name="valid_until" id="valid_until" class="form-control"
                                v-model="promocode.valid_until">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="title" class="col-sm-3 col-form-label">Description (intern use):</label>
                            <div class="col-sm-8">
                            <textarea name="description" id="description" cols="30" rows="2" class="form-control"
                                v-model="promocode.description"></textarea>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="title" class="col-sm-3 col-form-label">Set discount</label>
                            <div class="col-sm-8">

                                <div class="row shadow-sm  bg-white rounded">
                                    <div class="col-sm-5 pb-3">
                                        <input type="radio" id="one" value="perc" v-model="discount_type">Percentage
                                    </div>
                                    <div class="col-sm-4" v-if="discount_type=='perc'">
                                        <input required type="number" step="0.01" min="0" name="discount_perc" id="discount_perc" class="form-control"
                                        v-model="promocode.discount_perc">

                                    </div>
                                    <div class="col-sm-1" v-if="discount_type=='perc'">
                                        %
                                    </div>
                                </div>

                                <div class="row shadow-sm bg-white rounded">
                                    <div class="col-sm-5  pb-3">
                                        <input type="radio" id="two" value="amount" v-model="discount_type">Amount
                                    </div>
                                    <div class="col-sm-4"  v-if="discount_type=='amount'">
                                        <input required type="number" step="0.01" min="0" name="discount_amount" id="discount_amount" class="form-control"
                                        v-model="discount_amount_div_100">
                                    </div>
                                     <div class="col-sm-1" v-if="discount_type=='amount'">
                                       €
                                    </div>
                                </div>

                                <div class="form-group row row shadow-sm bg-white rounded">

                            <div class="col-sm-8 ">
                                <input type="checkbox" :value="true" v-model="promocode.apply_to_tickets"> Apply to tickets<br>
                                <input type="checkbox" :value="false" v-model="promocode.apply_to_extras"> Apply to extras<br>
                                <input type="checkbox" :value="false" v-model="promocode.pay_by_invoice"> Payment by invoice<br>
                                <small class="form-text text-muted">When payment by invoice is selected an invoice will be sent and the payment step will be skipped</small>
                            </div>
                        </div>




                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="active" class="col-sm-3 col-form-label">remarks on guestlist</label>
                            <div class="col-sm-8">
                            <input required type="text" name="title" id="title" class="form-control"
                                v-model="promocode.remark_on_guestlist">
                            <small class="form-text text-muted">Leave blank for no remark on guest list.<br>The guest list is a printable list with all sales/reservations.</small>
                            </div>
                        </div>

                        </div><!--modal body-->

                        <div class="modal-footer">
                            <!-- in case of add-->
                            <span v-if="add_update=='add'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="createPromoCode" class="btn btn-primary">Create</button>
                        </span>
                            <!-- in case of update-->
                            <span v-if="add_update=='update'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="updatePromoCode" class="btn btn-primary">Update</button>
                        </span>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div><!--container-->
</template>


<script>
import DatePicker from 'v-calendar/lib/components/date-picker.umd';



export default {
    components: {
    DatePicker,
  },
  data() {
    return {

        //promocode data
        promocode: {
            code: "",
            description: "",
            discount_amount: "",
            discount_perc: "",
            apply_to_tickets:true,
            apply_to_extras:false,
            pay_by_invoice:false,
            remark_on_guestlist: "",

        },
        discount_type:"perc",
        errors: "",
        promocodes: [],
        message: "",
        add_update: "",
        show: false,
        discount_amount_div_100:"",
        discount_type:"",


    };

  },

  mounted() {
    this.readPromoCodes();
  },

  methods: {
    initAddPromoCode() {
      this.errors = "";
      this.add_update = "add";
      $("#add_promocode_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createPromoCode() {
        console.log(this.discount_type);
        this.promocode.discount_amount = this.discount_amount_div_100*100;
        if (this.discount_type=="perc") this.promocode.discount_amount = 0;
        if (this.discount_type=="amount") this.promocode.discount_perc = 0;

        console.log(this.promocode);
      axios
        .post("/admin/promocode", this.promocode )

        .then(response => {
          $("#add_promocode_model").modal("hide");
          //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
          this.readPromoCodes();
          this.showMessage(response.data.message);
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    showMessage(message) {
      this.message = message;
      this.show = true;
    },

    reset() {
        this.readPromoCodes();
    },

    readPromoCodes() {
        axios.get("/admin/promocode").then(response => {
        this.promocodes = response.data.promocodes;
      });
    },



    initUpdate(index) {

      this.errors = "";
      this.add_update = "update";



      $("#add_promocode_model").modal("show");
      this.promocode = this.promocodes[index];
     console.log(this.promocode.discount_amount);
      if (this.promocode.discount_amount){
          this.discount_amount_div_100 = this.promocode.discount_amount / 100;
          this.discount_type = "amount";
      }
      else{
          this.discount_type = "perc";
      }
    },

    updatePromoCode() {
    console.log(this.discount_type);
    this.promocode.discount_amount = this.discount_amount_div_100*100;
    if (this.discount_type=="perc") this.promocode.discount_amount = 0;
    if (this.discount_type=="amount") this.promocode.discount_perc = 0;

    console.log(this.promocode);
      axios
        .put("/admin/promocode/" + this.promocode.id, this.promocode)

        .then(response => {
          $("#add_promocode_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    deletePromoCode(index) {
      let conf = confirm(
        'Do you ready want to delete promocode "' +
          this.promocodes[index].code +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("/admin/promocode/" + this.promocodes[index].id)

          .then(response => {
            this.promocodes.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
            // Error
          });
      }
    },


  }
};
</script>
