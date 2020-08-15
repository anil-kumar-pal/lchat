<template>
    <div class="row">
        <div class="col-8">
            <div class="card card-default">
                <div class="card-header">Messages</div>
                <div class="card-body p-0">
                    <ul class="list-unstyle" style="height: 300px; overflow-y: scroll;" v-chat-scroll>
                        <li class="p-2" v-for="(message, index) in messages" :key="index" >
                            <strong>{{ message.user.name }}</strong>
                            {{ message.message }}
                        </li>
                    </ul>
                </div>
                <input
                     @keydown="sendTypingEvent"
                     @keyup.enter="sendMessage"
                     v-model="newMessage"
                     type="text"
                     name="message"
                     placeholder="Enter your message..."
                     class="form-control">
            </div>
            <div class="text-muted" v-if="activeUser">{{activeUser.name}} is typing... </div>
        </div>
        <div class="col-4">
            <div class="card card-default">
                <div class="card-header">Active Users</div>
                <div class="card-body">
                    <ul style="list-style-type: none;">
                        <li class="py-2" v-for="(user, index) in receivers" :key="index" v-on:click="setActiveReceiver(user)" :class="{ active : activeReceiver.id == user.id }">
                            {{user.name}}
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
         props:['user'],
        data() {
            return {
                messages: [],
                newMessage: '',
                receivers:[],
                users:[],
                activeReceiver:false,
                activeUser: false,
                typingTimer: false,
            }
        },
        created() {
            // this.fetchMessages();
            this.fetchReceivers();
            Echo.join('App.User.'+this.user.id)
                .here(user => {
                     console.log("Joined my private channle: Auth.User." + this.user.id);
                     this.users = user;
                })
                .joining(user => {
                     this.users.push(user);
                })
                .leaving(user => {
                    this.users = this.users.filter(u => u.id != user.id);
                })
                .listen('MessageSent',(event) => {
                    // if the message is from the current active user then add the message to message bord otherwise show the bell icon on sender name li in right bar  
                    if(event.messages.receiver_id == this.activeReceiver.id) {
                        this.messages.push(event.message);
                    }else {

                        // if the sender is new, that is not exit in the sender list of right bar push the user in Receivers variable
                        //this.receivers = event.message.user

                        // logic for bell icon on sender name

                    }

                })
                .listenForWhisper('typing', user => {
                    this.activeUser = user;
                    if(this.typingTimer) {
                        clearTimeout(this.typingTimer);
                    }
                    this.typingTimer = setTimeout(() => {
                        this.activeUser = false;
                    }, 3000);
                })
        },
        methods: {
            fetchMessages() {
                axios.get('messages?receiver='+this.activeReceiver.id).then(response => {
                    this.messages = response.data;
                })
            },
            fetchReceivers() {                
                axios.get('receivers').then(response => {
                    console.log(response.data.length)
                    this.receivers = response.data;
                    if(response.data.length) {
                        this.activeReceiver = response.data[0];
                        this.fetchMessages();
                    }
                })
            },
            sendMessage() {
                this.messages.push({
                    user: this.user,
                    message: this.newMessage
                });
                axios.post('messages', {
                    message: this.newMessage,
                    receiver : this.activeReceiver.id,
                });
                this.newMessage = '';
            },
            sendTypingEvent() {
                Echo.join('chat')
                    .whisper('typing', this.user);
            },
            setActiveReceiver(user) {
                console.log(user);
                //setting new active receiver
                this.activeReceiver = user;
                //fetching the message for new active receivers
                this.fetchMessages()
            }
         }
     }
</script>
