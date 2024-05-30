<template>
    <div class="container">
        <div class="row">
            <div class="col-md-3 bg-light sidebar" v-if="showRoomForm == false">
                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    "
                >
                    <h5 class="mt-3">Chat Rooms</h5>
                    <button
                        style="height: 40px; margin-top: 2px"
                        @click="toggleRoomForm"
                    >
                        Add Room
                    </button>
                </div>
                <ul class="list-group">
                    <li
                        class="list-group-item"
                        v-for="room in rooms"
                        :key="room.id"
                        @click="showMessageList(room.id)"
                    >
                        {{ room.name }}
                    </li>
                </ul>
            </div>

            <div class="col-md-3 bg-light sidebar" v-if="showRoomForm == true">
                <form @submit.prevent="addRoom">
                    <div class="form-group">
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            placeholder="Enter Room Name"
                            v-model="room"
                        />
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </form>
            </div>

            <div
                class="col-md-9"
                style="
                    display: flex;
                    justify-content: center;
                    align-items: center;
                "
                v-if="showMessages == false"
            >
                <h2>Join a chat room</h2>
            </div>

            <div
                class="col-md-9"
                style="position: relative; height: 70vh"
                v-if="showMessages == true"
            >
                <div class="chat-container p-3 bg-white">
                    <div
                        class="chat-message"
                        v-for="message in messages"
                        :key="message.id"
                    >
                        <span style="font-size: 12px; margin-right: 12px">{{
                            message.user.name
                        }}</span>
                        {{ message.content }}
                    </div>
                </div>
                <form
                    @submit.prevent="sendMessage"
                    class="mt-3"
                    style="position: absolute; left: 0; bottom: 0; right: 0"
                >
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Type a message"
                            v-model="message.content"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                Send
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, reactive } from "vue";

export default {
    setup() {
        const rooms = ref([]);
        const messages = ref([]);
        let roomId = ref(0);
        let message = reactive({
            content: "",
            room_id: "",
        });
        const room = ref("");
        let showRoomForm = ref(false);
        let showMessages = ref(false);

        const fetchRooms = async () => {
            try {
                const response = await axios.get("/api/rooms");
                rooms.value = response.data.data;
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
        };

        const toggleRoomForm = () => {
            showRoomForm.value = true;
        };

        const toggleShowMessages = () => {
            showMessages.value = true;
        };

        const addRoom = async () => {
            try {
                const response = await axios.post("/api/rooms", {
                    name: room.value,
                });
                fetchRooms();
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
            showRoomForm.value = false;
        };

        const sendMessage = async () => {
            try {
                const response = await axios.post("/api/messages", message);
                if (response.data.success) {
                    messages.value = [...messages.value, response.data.data];
                }
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
            showRoomForm.value = false;
            message.content = "";
        };

        async function showMessageList(room_id) {
            message.room_id = room_id;
            try {
                const response = await axios.get(`/api/messages/${room_id}`);
                messages.value = response.data.data;
                toggleShowMessages();
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
        }

        onMounted(() => {
            fetchRooms();
            window.Echo.channel("chat-room").listen("MessageSent", (event) => {
                messages.value = [...messages.value, event.message];
            });
        });

        return {
            rooms,
            showRoomForm,
            toggleRoomForm,
            room,
            addRoom,
            showMessages,
            showMessageList,
            messages,
            message,
            sendMessage,
        };
    },
};
</script>
