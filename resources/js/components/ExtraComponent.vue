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
                <div class="card-header">Extras


                </div>
                <!--card header-->
                <div class="card-body">
                    <button @click="initAddExtra()" class="btn btn-primary btn-xs float-left">
                            + Add New Extra
                    </button>
                    <br><hr>
                    <table class="table table-striped table-responsive table-sm" v-if="extras.length > 0" ref="table">
                        <thead>
                            <tr>
<!--
 $table->bigIncrements('id');
$table->string('title');//public title
$table->text('description')->nullable()->default(NULL);//public description
$table->string('admin_notes')->nullable()->default(NULL);//as the title can be the same, we may need the admin notes to be able to differentiate
$table->integer('price')->default('0');//price can be negative
$table->unsignedInteger('vat')->nullable()->default(NULL);//nullable for when you don't want to work with vat
$table->tinyInteger('order')->default('0');//in which order a category should be displayed
$table->boolean('show_on_door_list')->default(true);
$table->string('type');//
$table->jsonb('properties')->nullable()->default(NULL);
-->
                                <th>Title</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Max per sale</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>


                            <tr v-for="(extra, index) in extras" :key="extra.id">

                                <td>
                                    {{ extra.title }}
                                </td>
                                <td>
                                    {{ extra.description }}
                                </td>
                                <td>
                                    {{ extra.price }}
                                </td>
                                <td>
                                    {{ extra.max }}
                                </td>

                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteExtra(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>no extras yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->

    <div class="modal fade" tabindex="-1" role="dialog" id="add_extra_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">
                        <span v-if="add_update=='add'">Add New Extra(s)</span><span v-else>Edit Extra</span>
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
                            <label for="name" class="col-sm-3 col-form-label">Title:</label>
                            <div class="col-sm-8">
                            <input required type="text" name="title" id="title" class="form-control"
                                v-model="extra.title">
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="title" class="col-sm-3 col-form-label">Description (Optional):</label>
                            <div class="col-sm-8">
                            <textarea name="description" id="description" cols="30" rows="2" class="form-control"
                                v-model="extra.description"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                        <label for="title">Admin notes (Optional, internal use only):</label>

                        <textarea name="admin_notes" id="admin_notes" cols="30" rows="2" class="form-control"
                            v-model="extra.admin_notes"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="order">Order:</label>
                            <input required type="number" step="1" min="1" name="order" id="order" class="form-control"
                                    v-model="extra.order">
                            <small class="form-text text-muted">extra display order</small>
                        </div>


                         <div class="form-group">
                        <label for="price">Price (in cents):</label>
                        <input required type="number" step="1" min="0" name="price" id="price" class="form-control"
                                v-model="extra.price">
                        </div>

                        <div class="form-group">
                            <label for="vat">VAT (percentage):</label>
                            <input required type="number" step="1" min="0" max="100" name="vat" id="vat" class="form-control"
                                    v-model="extra.vat">
                        </div>



                        <div class="form-group row">
                            <label for="max_per_sale" class="col-sm-3 col-form-label">Max amount per sale</label>
                            <div class="col-sm-8">
                            <input type="text" class="form-control form-control-sm" name="max_per_sale" v-model="extra.max">
                            <small class="form-text text-muted">Optional, no value entered means free</small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="active" class="col-sm-3 col-form-label">show this extra on the guest list</label>
                            <div class="col-sm-8">
                            <select class="form-control form-control-sm" name="active" v-model="extra.show_on_door_list">
                                <option value="1" selected>Yes</option>
                                <option value="0">No</option>
                            </select>
                            <small class="form-text text-muted">the guest list is a printable list with all sales/reservations.</small>
                            </div>
                        </div>






                        </div>
                        <!--modal body-->

                        <div class="modal-footer">
                            <!-- in case of add-->
                            <span v-if="add_update=='add'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="createExtra" class="btn btn-primary">Create</button>
                        </span>
                            <!-- in case of update-->
                            <span v-if="add_update=='update'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="updateExtra" class="btn btn-primary">Update</button>
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

        //extra data
        extra: {
            title: "",
            description: "",
            admin_notes: "",
            max: "",
            show_on_door_list: "1"

        },
        errors: "",
        extras: [],
        message: "",
        add_update: "",
        show: false,
        dateValue: "",


    };

  },

  mounted() {
    this.readExtras();
  },

  methods: {
    initAddExtra() {
      this.errors = "";
      this.extra.extra_date = "";
      this.date="";
      this.add_update = "add";
      $("#add_extra_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createExtra() {
      axios
        .post("/admin/extra", this.$data.extra )

        .then(response => {
          $("#add_extra_model").modal("hide");
          //refresh table on screen (there may be a better way of doing this) *verbeterpunt*
          this.readExtras();
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
        this.readExtras();
    },

    readExtras() {
        axios.get("/admin/extra").then(response => {
        this.extras = response.data.extras;

      });
    },



    initUpdate(index) {

      this.errors = "";
      this.add_update = "update";
      $("#add_extra_model").modal("show");
      this.extra = this.extras[index];
    },

    updateExtra() {
      axios
        .put("/admin/extra/" + this.extra.id, this.$data.extra)

        .then(response => {
          $("#add_extra_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    deleteExtra(index) {
      let conf = confirm(
        'Do you ready want to delete extra "' +
          this.extras[index].title +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("/admin/extra/" + this.extras[index].id)

          .then(response => {
            this.extras.splice(index, 1);
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
