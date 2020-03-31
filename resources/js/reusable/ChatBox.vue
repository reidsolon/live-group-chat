<template>
    <div class="card">
        <div class="card-header container-fluid">
            <div class="row">
                <div class="col-9">
                    {{route.room_name}} 
                    <template v-if="!joined">
                        (You haven't join this room yet.)
                    </template>
                </div>
                <div class="col-3" v-if="route.room_type == 'm'" style="text-align: right;">
                    <ion-icon name="settings-outline"></ion-icon>
                </div>
            </div>
            
        </div>

        <div class="card-body msger" id="chatLogs">
            <!-- <div class="d-flex justify-content-center" v-if="condition">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div> -->
                <main class="msger-chat">
                    <!-- DEFAULT MESSAGE -->
                    <template v-if="data.messages_row.length < 1">
                        <div class="msg left-msg">
                            <div
                            class="msg-img"
                            style="background-image: url(https://image.flaticon.com/icons/svg/327/327779.svg)"
                            ></div>

                            <div class="msg-bubble">
                                <div class="msg-info">
                                <div class="msg-info-name">CHAT ROOM BOT</div>
                                <div class="msg-info-time">--:--</div>
                                </div>

                                <div class="msg-text">
                                    Hi, welcome to Live Chat Room!
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="msg" :class="{'right-msg' : message.participantID == data.parentData.userData.id }" v-for="(message, index) in data.messages_row" :key="index">
                            <div 
                            v-if="message.participantID != data.parentData.userData.id"
                            class="msg-img"
                            style="background-image: url(https://image.flaticon.com/icons/svg/145/145867.svg)"
                            ></div>

                            <div class="msg-bubble">
                                <div class="msg-info">
                                <div class="msg-info-name">{{message.userName}}</div>
                                <div class="msg-info-time">{{message.created_at | moment("from", 'now')}}</div>
                                </div>

                                <div class="msg-text">
                                    {{message.message}}
                                </div>
                            </div>
                        </div>
                        <div class="msg" alt="Someone is typing.." v-if="data.someoneIsTyping.bool && data.someoneIsTyping.user.id != data.parentData.userData.id ">
                            <div class="msg-bubble">
                                <div class="msg-text">
                                    <div class="ticontainer">
                                        <div class="tiblock">
                                            Someone is typing &nbsp;
                                            <div class="tidot"></div>
                                            <div class="tidot"></div>
                                            <div class="tidot"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                    </template>
                    
                </main>
        </div>
        <div class="card-footer" v-if="joined">
            <div class="input-group">
                <textarea v-model="data.message" @keyup="sendTypingEvent()" type="text" class="form-control p-1" :placeholder="`Say something to ${route.room_name} ...`"></textarea>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" @click="sendMessage()">
                        <div v-if="data.sending" class="d-flex justify-content-center">
                            <div class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div v-else>
                            Send
                        </div>
                    </button>
                </div>
            </div>
        </div>
        <div class="card-footer" v-else>
            <div class="input-group mb-3">
                <input type="text" v-model="join.room_pass" v-if="route.room_is_public == '0'" class="form-control" :placeholder="`Please enter ${route.room_name} room password...`">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" @click="joinRoom">Join Room</button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

import {pusher} from '../includes/pusher'
export default {
    data() {
        return {
            pusherVal: Object,
            route: {
                room_id: Number,
                room_name: '',
                room_type: '',
                room_is_public: Number,
            },
            joined: false,

            //join room 
            join: {
                room_pass: '',
            },
            data: {
                someoneIsTyping: {
                    user: {},
                    bool: false,
                },
                isBlank: false,
                isTyping: false,
                parentData: Object,
                messages_row: [],
                sending: false,
                message: '',
            }
        }
    },
    watch: {
        '$route' : 'setRoute'
    },
    methods: {
        _initPusher() {
            this.pusherVal = pusher

            // -- NEW ROOM LISTENER
            const newMessage = this.pusherVal.subscribe(`chat-${this.route.room_id}`)
            newMessage.bind('new-message', (data) => {
                this._getRoomMessages(this.route.room_id)
            })

            // -- LISTEN TYPING
            const typing = this.pusherVal.subscribe(`typing-room-${this.route.room_id}`)
            typing.bind('is-typing', (data) => {
                
                if(data.isTyping) {
                    this.data.someoneIsTyping.bool = true
                    this.data.someoneIsTyping.user = data.user
                } else {
                    this.data.someoneIsTyping.bool = false
                    this.data.someoneIsTyping.user = data.user
                }
            })
            
        },
        sendTypingEvent() {
            var payload = {}
            if(this.data.message != '' && this.data.message.length == 1)
            {
                this.data.isTyping = true
                payload = {
                    room_id: this.route.room_id,
                    isTyping: this.data.isTyping
                }
                
                axios.post('/isTyping', payload)
                .then( res => {
                    if(res.status == 200) {
                        this.data.isBlank = false
                    }
                })
                .catch( err => {
                    console.log(err)
                })

            } else if(this.data.message == '' && !this.data.isBlank) {
                this.data.isTyping = false
                payload = {
                    room_id: this.route.room_id,
                    isTyping: this.data.isTyping
                }

                axios.post('/isTyping', payload)
                .then( res => {
                    if( res.status == 200) {
                        this.data.isBlank = true
                    }
                })
                .catch( err => {
                    console.log(err)
                })

            }
        },
        scrollTop() {
            $(".msger-chat").stop().animate({ scrollTop: $(".msger-chat")[0].scrollHeight}, 1500);
        },
        _getRoomMessages(id) {
            const payload = {
                room_id: this.route.room_id
            }
            axios.post('/room/getMessages', payload)
            .then( res => {
                if(res.status == 200) {
                    if(res.data.status == 1) {
                        this.data.messages_row = res.data.rows
                        this.scrollTop()
                    } else if(res.data.status == -1) {
                        this.data.messages_row = []
                        this.$toastr.w(res.data.message)
                    } else {
                        this.data.messages_row = []
                        this.toastr.w(res.data.message)
                    }
                } else {
                    this.toastr.w(`Error with status code of ${res.status}`)
                }
            })
            .catch( err => {
                this.$toastr.e(err)
            })
        },
        sendMessage() {
            this.data.sending = true
            if(this.data.message != '' && this.data.message != null) {
                const payload = {
                    message: this.data.message,
                    room_id: this.route.room_id
                }
                axios.post('/user/sendMessage', payload)
                .then( res => {
                    this.data.sending = false
                    if(res.status == 200) {
                        if(res.data.status == 1) {
                            this.data.message = ''
                            this.sendTypingEvent()
                        } else {
                            this.$toastr.w(res.data.message)
                        }
                    } else {
                        this.$toastr.w(`Status code ${res.status}`, 'Request Failed')
                    }
                })
                .catch( err => {
                    this.$toastr.e(err)
                })

            } else {
                this.$toastr.w('You have empty message!', 'Dont be silly!')
            }
        },
        setRoute() {
            this.data.messages_row = []
            this.route.room_name = this.$route.params.room_name
            this.route.room_type = this.$route.params.room_type
            this.route.room_id   = this.$route.params.room_key
            this.route.room_is_public = this.$route.params.room_is_public

            this.checkIfJoined()
            this._initPusher()
        },
        checkIfJoined() {
            const payload = {
                roomID: this.route.room_id
            }
            axios.post('/room/isJoined', payload)
            .then( res => {
                if(res.status == 200) {
                    if(res.data.status == 1) {
                        this.joined = true
                        if(this.joined) {
                            this._getRoomMessages(this.route.room_id)
                        }
                    } else {
                        this.joined = false
                    }
                } else {
                    console.log(res.status)
                }
            })
            .catch( err => {
                console.error(err)
            })
        },
        joinRoom() {
            const payload = {
                room_id: this.route.room_id,
                room_pass: this.join.room_pass,
                room_is_public: parseInt(this.route.room_is_public)
            }

            axios.post('/room/join', payload)
            .then( res => {
                if(res.status == 200) {
                    if(res.data.status == 1) {
                        this.$toastr.s(res.data.message, "Success");
                        this.$parent.getActiveRooms()
                        this.checkIfJoined()
                    } else {
                        this.$toastr.e(res.data.message, "Failed");
                    }
                } else { 

                }
            })
            .catch( err => {
                console.error(err)
            })
        }
    },
    mounted() {
        this.setRoute()
        this.data.parentData = this.$parent.data
    }
}
</script>
<style scoped>
#chatLogs {
    height: 50vh;
}
textarea {resize: none;}
</style>