<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-sm-6 mt-4">
                <h2 class="text-center">Register</h2>
                <p class="text-danger" v-if="error">{{ error }}</p>
                <form @submit.prevent="register">
                    <div class="form-group">
                        <label for="name">Name:</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            v-model="form.name"
                        />
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address:</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            v-model="form.email"
                        />
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            v-model="form.password"
                        />
                    </div>
                    <div class="form-group">
                        <label for="c_password">Confirm Password:</label>
                        <input
                            type="password"
                            class="form-control"
                            id="c_password"
                            v-model="form.c_password"
                        />
                    </div>
                    <button type="submit" class="btn btn-primary">
                        Register
                    </button>
                </form>

                <div class="mt-4">
                    Already have an account ?
                    <router-link to="/login">Login</router-link>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";
import { useStore } from "vuex";

export default {
    setup() {
        const router = useRouter();
        const store = useStore();
        let form = reactive({
            name: "",
            email: "",
            password: "",
            c_password: "",
        });
        let error = ref("");

        const register = async () => {
            await axios.post("/api/register", form).then((res) => {
                if (res.data.success) {
                    store.dispatch("setToken", res.data.data.token);
                    router.push({ name: "Home" });
                } else {
                    error.value = res.data.message;
                }
            });
        };

        return { form, register, error };
    },
};
</script>
