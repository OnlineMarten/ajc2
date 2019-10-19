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
                <div class="card-header">Categories

                </div>
                <!--card header-->

                <div class="card-body">
                    <button @click="initAddCategory()" class="btn btn-primary btn-xs float-left">
                            + Add New Category
                    </button>
                    <table class="table  table-striped table-responsive table-sm" v-if="categories.length > 0" ref="table">
                        <thead>
                            <tr>
                                <th>Order</th>
                                <th>Title</th>
                                <th>Description</th>
                                <th>Admin notes</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(category, index) in categories" :key="category.id">
                                <td>
                                    {{ category.order }}
                                </td>
                                <td>
                                    {{ category.title }}
                                </td>
                                <td>
                                    {{ category.description }}
                                </td>
                                <td>
                                    {{ category.admin_notes }}
                                </td>
                                <td>

                                    <button @click="initUpdate(index)" class="btn btn-success btn-sm"><i class="fas fa-edit"></i>Edit</button>
                                    <button @click="deleteCategory(index)" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i>Delete</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                     <div v-else>
                        <br><hr>
                        <p>no categories yet</p>
                    </div>
                </div>
                <!--card body-->

            </div>
            <!--card-->

        </div>
        <!--col-->
    </div>
    <!--row-->

    <div class="modal fade" tabindex="-1" role="dialog" id="add_category_model">

        <!--MODAL ADD UPDATE-->
        <div class="modal-dialog" role="document">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 class="modal-title">Add New Category</h4>
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
                        <label for="order">Order:</label>
                        <input required type="number" step="1" min="1" name="order" id="order" class="form-control"
                                v-model="category.order">
                        </div>

                        <div class="form-group">
                            <label for="name">Title:</label>
                            <input required type="text" name="title" id="title" class="form-control"
                                v-model="category.title">

                        </div>

                        <div class="form-group">
                            <label for="title">Description (Optional):</label>

                            <textarea name="description" id="description" cols="30" rows="5" class="form-control"
                                v-model="category.description"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="title">Admin notes (Optional, internal use only):</label>

                            <textarea name="admin_notes" id="admin_notes" cols="30" rows="5" class="form-control"
                                v-model="category.admin_notes"></textarea>
                        </div>

                        </div>
                        <!--modal body-->

                        <div class="modal-footer">
                            <!-- in case of add-->
                            <span v-if="add_update=='add'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="createCategory" class="btn btn-primary">Create</button>
                        </span>
                            <!-- in case of update-->
                            <span v-if="add_update=='update'">
                        <button type="button" @click="reset" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" @click="updateCategory" class="btn btn-primary">Update</button>
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
      category: {
        order: "",
        title: "",
        description: "",
        admin_notes: ""
      },
      errors: "",
      categories: [],
      message: "",
      add_update: "",
      show: false
    };
  },

  mounted() {
    this.readCategories();
  },

  methods: {
    initAddCategory() {
      this.errors = "";
      this.add_update = "add";
      $("#add_category_model").modal("show");
    },

    showErrors(error) {
      this.errors = "<ul>";
      let response = error.response;
      Object.keys(response.data.errors).forEach(item => {
        this.errors += "<li>" + response.data.errors[item] + "</li>";
      });
      this.errors += "</ul>";
    },

    createCategory() {
      axios
        .post("category", this.$data.category)

        .then(response => {
          $("#add_category_model").modal("hide");
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
      this.category.order = "";
      this.category.title = "";
      this.category.description = "";
      this.category.admin_notes = "";
      this.readCategories();
    },

    readCategories() {
      axios.get("category").then(response => {
        this.categories = response.data.categories;
      });
    },



    initUpdate(index) {
      this.errors = "";
      this.add_update = "update";
      $("#add_category_model").modal("show");
      this.category = this.categories[index];
    },

    updateCategory() {
      axios
        .put("category/" + this.category.id, this.$data.category)

        .then(response => {
          $("#add_category_model").modal("hide");
          this.showMessage(response.data.message);
          this.reset();
        })

        .catch(error => {
          this.showErrors(error);
        });
    },

    deleteCategory(index) {
      let conf = confirm(
        'Do you ready want to delete category "' +
          this.categories[index].title +
          '"?'
      );
      if (conf === true) {
        axios
          .delete("category/" + this.categories[index].id)

          .then(response => {
            this.categories.splice(index, 1);
            this.showMessage(response.data.message);
          })

          .catch(error => {
            this.showMessage(error.response.data.message);
            // Error
          });
      }
    }
  }
};
</script>
