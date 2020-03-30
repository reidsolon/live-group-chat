<template>
    <div class="card">
        <div class="card-header container-fluid">
            <div class="row">
                <div class="col-6">
                    {{route.room_name}} 
                    <template v-if="!joined">
                        (You haven't join this room yet.)
                    </template>
                </div>
                <div class="col-6" v-if="route.room_type == 'm'" style="text-align: right;">
                    <ion-icon name="settings-outline"></ion-icon>
                </div>
            </div>
            
        </div>

        <div class="card-body" id="chatLogs">
            <div class="d-flex justify-content-center" v-if="condition">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="card-footer" v-if="joined">
            <div class="input-group">
                <textarea v-model="data.message" type="text" class="form-control p-1" :placeholder="`Say something to ${route.room_name} ...`"></textarea>
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
export default {
    data() {
        return {
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
        _getRoomMessages(id) {
            const payload = {
                room_id: this.route.room_id
            }
            axios.post('/room/getMessages', payload)
            .then( res => {
                if(res.status == 200) {
                    if(res.data.status == 1) {
                        this.data.messages_row = res.data.rows
                    } else if(res.data.status == -1) {
                        this.$toastr.w(res.data.message)
                    } else {
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
            this.route.room_name = this.$route.params.room_name
            this.route.room_type = this.$route.params.room_type
            this.route.room_id   = this.$route.params.room_key
            this.route.room_is_public = this.$route.params.room_is_public

            this.checkIfJoined()
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
        
    }
}
</script>
<style scoped>

#chatLogs {
    height: 50vh;
}
textarea {resize: none;}
</style>