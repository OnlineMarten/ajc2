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
                <div class="card-header">Tickets

                </div>
                <!--card header-->

                <div class="card-body">
                    <button @click="initAddTicket()" class="btn btn-primary btn-xs float-left">
                            + Add New Ticket
                    </button>
                    <table class="table  table-striped table-responsive table-sm" v-if="tickets.length > 0" ref="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Admin notes</th>
                                <th>Price</th>
                                <th>VAT</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(ticket, index) in tickets" :key="ticket.id">
                                <td>
                                    {{ ticket.order }}
                                </td>
                                <td>
                                    {{ ticket.title }}
                                </td>
                                <td>
                                    {{ ticket.description }}
                                </td>
                                <td>
                                    {{ ticket.admin_notes }}
                                </td>
                                <td>
                                    &euro; {{ toCurrency(ticket.price) }}
                                </td>
                                <td>
                                    {{ ticket.vat }} &percnt;
                                </td>
                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteTicket(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div v-else>
                        <br><hr>
                        <p>no tickets yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->

    <div class="modal fade" tabindex="-1" role="dialog" id="add_ticket_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Add New Ticket</h4>
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



                    <div class="form-group">
                        <label for="name">Title:</label>
                        <input required type="text" name="title" id="title" class="form-control"
                            v-model="ticket.title">

                    </div>

                    <div class="form-group">
                        <label for="title">Description (Optional):</label>

                        <textarea name="description" id="description" cols="30" rows="2" class="form-control"
                            v-model="ticket.description"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="title">Admin notes (Optional, internal use only):</label>

                        <textarea name="admin_notes" id="admin_notes" cols="30" rows="2" class="form-control"
                            v-model="ticket.admin_notes"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="order">Order:</label>
                        <input required type="number" step="1" min="1" name="order" id="order" class="form-control"
                                v-model="ticket.order">
                        <small class="form-text text-muted">ticket display order</small>
                    </div>

                    <div class="form-group">
                        <label for="price">Price (in cents):</label>
                        <input required type="number" step="1" min="0" name="price" id="price" class="form-control"
                                v-model="ticket.price">
                    </div>

                    <div class="form-group">
                        <label for="vat">VAT (percentage):</label>
                        <input required type="number" step="1" min="0" max="100" name="vat" id="vat" class="form-control"
                                v-model="ticket.vat">
                    </div>

                </div><!--modal body-->

                    <div class="modal-footer">
                        <!-- in case of add-->
                        <span v-if="add_update=='add'">
                            <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" @click="createTicket" class="btn btn-primary">Create</button>
                        </span>
                        <!-- in case of update-->
                        <span v-if="add_update=='update'">
                            <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="button" @click="updateTicket" class="btn btn-primary">Update</button>
                        </span>
                    </div>
                </div><!-- /.modal-content -->
            </div><!-- /.modal-dialog -->
        </div><!-- /.modal -->
    </div><!--container-->
</template>


<script>
export default {
  data() {
    return {
      ticket: {
        title: "",
        description: "",
        admin_notes: "",
        price: "",
        vat: "",
        order: ""
      },
      errors: "",
      tickets: [],
      message: "",
      add_update: "",
      show: false
    };
  },

  mounted() {
    this.readTickets();
  },

    computed: {
    computedScore () {
        return this.score.toFixed(2);
    },
    },

  methods: {
    initAddTicket() {
      this.errors = "";
      this.add_update = "add";
      $("#add_ticket_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createTicket() {
      axios
        .post("ticket", this.$data.ticket)

        .then(response => {
          $("#add_ticket_model").modal("hide");
          //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
          this.showMessage(response.data.message);
          this.reset();
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
      this.ticket.order = "";
      this.ticket.title = "";
      this.ticket.description = "";
      this.ticket.admin_notes = "";
      this.readTickets();
    },

    readTickets() {
      axios.get("ticket").then(response => {
        this.tickets = response.data.tickets;
      });
    },



    initUpdate(index) {
      this.errors = "";
      this.add_update = "update";
      $("#add_ticket_model").modal("show");
      this.ticket = this.tickets[index];
    },

    updateTicket() {
      axios
        .put("ticket/" + this.ticket.id, this.$data.ticket)

        .then(response => {
          $("#add_ticket_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    deleteTicket(index) {
      let conf = confirm(
        'Do you ready want to delete ticket "' +
          this.tickets[index].title +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("ticket/" + this.tickets[index].id)

          .then(response => {
            this.tickets.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
            // Error
          });
      }
    },



    toCurrency (val) {

        return (val/100).toFixed(2);
    },
  }
};
</script>
