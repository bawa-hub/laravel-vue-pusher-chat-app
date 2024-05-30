<template>
    <div>
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <router-link class="navbar-brand" to="/">ChatApp</router-link>
            <button
                type="button"
                class="btn btn-dark"
                style="margin-left: auto"
                @click="logout"
                v-if="$store.getters.getToken != 0"
            >
                Logout
            </button>
        </nav>

        <router-view></router-view>
    </div>
</template>

<script>
import { useRouter } from "vue-router";
import { useStore } from "vuex";

export default {
    setup() {
        const router = useRouter();
        const store = useStore();

        function logout() {
            store.dispatch("removeToken");
            router.push({ name: "Login" });
        }

        return { logout };
    },
};
</script>
