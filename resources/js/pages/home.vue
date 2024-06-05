<template>
    <div class="container">
        <div class="row">
            <!-- list of rooms -->
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

            <!-- add room -->
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

            <!-- no room selected -->
            <div
                class="col-md-6"
                style="
                    display: flex;
                    justify-content: center;
                    align-items: center;
                "
                v-if="showMessages == false"
            >
                <h2>Join a chat room</h2>
            </div>

            <!-- message container  -->
            <div
                class="col-md-6"
                style="
                    display: flex;
                    justify-content: space-between;
                    flex-direction: column;
                    height: 70vh;
                "
                v-if="showMessages == true"
            >
                <div style="margin-bottom: 10px">
                    <input
                        type="text"
                        placeholder="Search message"
                        v-model="searchQuery"
                        @change="searchMessage"
                    />
                </div>
                <div
                    class="chat-container p-3 bg-white"
                    style="
                        height: 60vh;
                        overflow-y: scroll;
                        padding-bottom: 10px;
                    "
                    @scroll="handleScroll"
                >
                    <div
                        class="chat-message"
                        v-for="message in messages"
                        :key="message.id"
                        style="padding-bottom: 4px; display: flex"
                    >
                        <div
                            style="
                                margin-right: 12px;
                                height: 30px;
                                width: 30px;
                                background-color: grey;
                                display: inline-block;
                                border-radius: 99px;
                                text-align: center;
                                padding: 2px;
                            "
                        >
                            {{ message.user.name.charAt(0) }}
                        </div>

                        <div
                            style="
                                background-color: #d9fdd3;
                                color: black;
                                font-weight: bold;
                                padding: 4px;
                                border-radius: 4px;
                            "
                        >
                            {{ message.content }}
                            <div v-if="message.file_path">
                                <a
                                    :href="getFileUrl(message.file_path)"
                                    target="_blank"
                                    >Download File</a
                                >
                            </div>
                            <span
                                style="
                                    color: gray;
                                    font-weight: bold;
                                    font-size: 8px;
                                    margin-top: 10px;
                                "
                                >{{ fetchTime(message.created_at) }}</span
                            >
                        </div>
                    </div>

                    <div style="height: 2vh"></div>
                </div>
                <form
                    @submit.prevent="sendMessage"
                    class="mt-3"
                    style="height: 10vh"
                >
                    <div class="input-group">
                        <input
                            type="text"
                            class="form-control"
                            placeholder="Type a message"
                            v-model="message.content"
                            @input="showTyping"
                        />
                        <input
                            type="file"
                            @change="handleFileUpload"
                            ref="fileInput"
                            style="display: none"
                        />
                        <div class="input-group-append">
                            <button
                                class="btn btn-primary"
                                type="button"
                                @click="triggerFileInput"
                                style="margin-left: 3px; margin-right: 3px"
                            >
                                Attach File
                            </button>
                            <button class="btn btn-primary" type="submit">
                                Send
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- list of online users -->
            <div class="col-md-3 bg-light sidebar" v-if="showMessages == true">
                <div
                    style="
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                    "
                >
                    <h5 class="mt-3">Online Users</h5>
                </div>
                <ul class="list-group">
                    <li
                        class="list-group-item"
                        v-for="user in onlineUsers"
                        :key="user.id"
                    >
                        {{ user.name }}
                        <span
                            style="font-size: 8px"
                            v-if="user.isTyping == true"
                            >typing...</span
                        >
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
import { ref, onMounted, reactive, nextTick } from "vue";
import store from "../store";
import { reverse } from "lodash";

export default {
    setup() {
        const rooms = ref([]);
        const messages = ref([]);
        const onlineUsers = ref([]);
        let roomId = ref(0);
        let message = reactive({
            content: "",
            room_id: "",
            file: null,
        });
        const room = ref("");
        let showRoomForm = ref(false);
        let showMessages = ref(false);
        let page = ref(1);
        let previousScrollHeightMinusScrollTop = ref(null);
        const fileInput = ref(null);
        const searchQuery = ref("");
        let previousSentHeartBeat = ref(null);

        const fetchRooms = async () => {
            try {
                const response = await axios.get("/api/rooms", {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });
                rooms.value = response.data.data;
            } catch (error) {
                console.error("Error fetching rooms:", error);
            }
        };

        const fetchOnlineUsers = async () => {
            try {
                const response = await axios.get("/api/users/online", {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });
                onlineUsers.value = response.data.data;
            } catch (error) {
                console.error("Error fetching rooms:", error);
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
            showTypingEnd();
            const formData = new FormData();
            formData.append("content", message.content);
            formData.append("room_id", message.room_id);
            if (message.file) {
                console.log("mesa.file", message.file);
                formData.append("file", message.file);
            }
            try {
                const response = await axios.post("/api/messages", formData, {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                        "Content-Type": "multipart/form-data",
                    },
                });
                if (response.data.success) {
                    messages.value = [...messages.value, response.data.data];
                    scrollToBottom();
                }
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
            showRoomForm.value = false;
            message.content = "";
            message.file = null;
        };

        const handleFileUpload = (event) => {
            message.file = event.target.files[0];
        };

        const triggerFileInput = () => {
            fileInput.value.click();
        };

        const getFileUrl = (filePath) => {
            return `/storage/${filePath}`;
        };

        async function showMessageList(room_id) {
            message.room_id = room_id;
            roomId.value = room_id;
            try {
                const response = await axios.get(`/api/messages/${room_id}`, {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });
                messages.value = reverse(response.data.data);
                toggleShowMessages();
                await nextTick();
                scrollToBottom();
                page.value = 2;
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
        }

        async function fetchNewMessage(room_id) {
            if (searchQuery.value) return;
            message.room_id = room_id;
            recordScrollPosition();
            try {
                const response = await axios.get(
                    `/api/messages/${room_id}?page=${page.value}`,
                    {
                        headers: {
                            Authorization: `Bearer ${store.getters.getToken}`,
                        },
                    }
                );

                messages.value = [
                    ...reverse(response.data.data),
                    ...messages.value,
                ];
                page.value += 1;

                toggleShowMessages();
                roomId.value = room_id;
                await nextTick();
                restoreScrollPosition();
            } catch (error) {
                console.error("Error fetching employees:", error);
            }
        }

        function recordScrollPosition() {
            let node = document.querySelector(".chat-container");
            previousScrollHeightMinusScrollTop.value =
                node.scrollHeight - node.scrollTop;
        }

        function restoreScrollPosition() {
            let node = document.querySelector(".chat-container");
            node.scrollTop =
                node.scrollHeight - previousScrollHeightMinusScrollTop.value;
        }

        const handleScroll = async () => {
            const chatContainer = document.querySelector(".chat-container");
            if (chatContainer.scrollTop === 0) {
                await fetchNewMessage(roomId.value);
            }
        };

        const scrollToBottom = () => {
            const chatContainer = document.querySelector(".chat-container");
            if (chatContainer)
                chatContainer.scrollTop = chatContainer.scrollHeight;
        };

        const fetchTime = (date) => {
            const isoString = date;
            const dateObject = new Date(isoString);

            const IST_OFFSET_HOURS = 5;
            const IST_OFFSET_MINUTES = 30;

            const utcMilliseconds = dateObject.getTime();

            const istMilliseconds =
                utcMilliseconds +
                IST_OFFSET_HOURS * 60 * 60 * 1000 +
                IST_OFFSET_MINUTES * 60 * 1000;

            const istDateObject = new Date(istMilliseconds);

            const hours = String(istDateObject.getUTCHours()).padStart(2, "0");
            const minutes = String(istDateObject.getUTCMinutes()).padStart(
                2,
                "0"
            );

            const formattedTime = `${hours}:${minutes}`;
            return formattedTime;
        };

        const searchMessage = async () => {
            if (!searchQuery.value) {
                messages.value = [];
                return;
            }

            try {
                const response = await axios.get("/api/search/messages", {
                    params: {
                        query: searchQuery.value,
                        room_id: roomId.value,
                    },
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });

                if (response.data.success) {
                    messages.value = response.data.data;
                    console.log("messages", messages.value);
                } else {
                    console.error("Search failed:", response.data.message);
                }
            } catch (error) {
                console.error("Error searching messages:", error);
            }
        };

        const showTyping = async () => {
            console.log("typoing");
            try {
                await axios.get("/api/users/typing", {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });
            } catch (error) {
                console.error("Error fetching rooms:", error);
            }
        };

        const showTypingEnd = async () => {
            console.log("typoingEnd");
            try {
                await axios.get("/api/users/endtyping", {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });
            } catch (error) {
                console.error("Error fetching rooms:", error);
            }
        };

        const sendHeartBeat = async () => {
            console.log("sendheartbeat", timeDiff());
            if (timeDiff() < 5) return; // Correctly call the function

            try {
                const response = await axios.get("/api/users/heartbeat", {
                    headers: {
                        Authorization: `Bearer ${store.getters.getToken}`,
                    },
                });

                previousSentHeartBeat.value = new Date();
            } catch (error) {
                console.error("Error sending heartbeat:", error);
            }
        };

        const timeDiff = () => {
            if (previousSentHeartBeat.value == null) return 1000;

            const datetime1 = previousSentHeartBeat.value;
            const datetime2 = new Date();

            // Calculate the difference in milliseconds
            const diffInMilliseconds = datetime2 - datetime1;

            // Convert milliseconds to seconds
            const diffInSeconds = diffInMilliseconds / 1000;

            return diffInSeconds;
        };

        onMounted(() => {
            fetchRooms();
            fetchOnlineUsers();

            setInterval(() => {
                fetchOnlineUsers();
            }, 7000);

            window.addEventListener("mousemove", sendHeartBeat);
            window.addEventListener("click", sendHeartBeat);
            window.addEventListener("keypress", sendHeartBeat);

            window.Echo.channel("chat-room").listen("MessageSent", (event) => {
                messages.value = [...messages.value, event.message];
            });

            window.Echo.channel("online-user").listen("OnlineUser", (event) => {
                onlineUsers.value = [...onlineUsers.value, event.message];
                console.log("online-user", onlineUsers.value);
            });

            window.Echo.channel("user-typing").listen("UserTyping", (event) => {
                console.log("usertyping event catch");
                onlineUsers.value = onlineUsers.value.map((user) =>
                    user.id == event.message.id
                        ? { ...user, isTyping: true }
                        : user
                );
                console.log("users", onlineUsers.value);
            });

            window.Echo.channel("user-end-typing").listen(
                "UserEndTyping",
                (event) => {
                    console.log("userEndtyping event catch");
                    onlineUsers.value = onlineUsers.value.map((user) =>
                        user.id == event.message.id
                            ? { ...user, isTyping: false }
                            : user
                    );
                    console.log("users", onlineUsers.value);
                }
            );
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
            handleScroll,
            fetchTime,
            handleFileUpload,
            triggerFileInput,
            getFileUrl,
            fileInput,
            searchMessage,
            searchQuery,
            onlineUsers,
            showTyping,
        };
    },
};
</script>
