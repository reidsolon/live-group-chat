<template>
    <div class="container">
        <Modal>
            <template slot="modal-title">
                New Chat Room
            </template>
            <template slot="modal-body">
                
                <template v-if="!validation.isValidated && validation.isChecked">
                    <span class="err">Please don't leave it blank before clicking join button! <br>You must provide:
                    <ul>
                        <li v-for="(requiredItem, index) in validation.required" :key="index">{{requiredItem}}</li>
                    </ul></span>
                </template>
                <template>
                    <input v-model="data.name" type="text" @focus="availability()" @blur="availability()" class="form-control required" placeholder="Enter a room name.." title="Room Name" />
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" v-model="data.isPrivate" value="">
                        <label class="form-check-label" for="defaultCheck1">
                            Make room private?
                        </label>
                    </div>
                    <template>
                        <a @click="availability()">Check availability</a>
                            <div v-if="loading.availableLoad" class="spinner-border" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                            <template v-if="validation.isChecked && !loading">
                                <template v-if="data.availability"> <strong><em>Available!</em></strong></template>
                                <template v-else><span class="err">Not available</span></template>
                            </template>
                        
                    </template>
                </template>
            </template>
            <template slot="modal-footer">
                <template v-if="!loading.creatingRoom">
                    <button class="btn btn-primary" @click="createRoom" v-if="data.availability">Create Room</button>
                </template>
                <template v-else>    
                    <button class="btn btn-primary" disabled >Creating room...</button>
                </template>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </template>
        </Modal>
        <div class="row justify-content-center">
            <div class="col-md-3 container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header container-fluid">
                                <div class="row">
                                    <div class="col-6" style="vertical-align: middle !important;"><ion-icon name="home-outline"></ion-icon> My Rooms</div>
                                </div>
                            </div>

                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-12 chat-room-item" data-toggle="modal" data-target="#exampleModal">
                                        Add New Room +
                                    </div> 
                                    <template v-if="loading.myRoomLoaded"> 
                                        <template v-if="data.myRooms.length > 0">
                                            <template v-for="(myroom, index) in data.myRooms">
                                                <router-link class="col-12 chat-room-item" :to="'/m/'+myroom.id+'/'+myroom.roomName+'/'+myroom.isPublic" :key="'ROUTER'+index">
                                                    <div class="" :key="index">
                                                        <div class="chat-room-title">
                                                            <strong>{{myroom.roomName}}</strong>
                                                        </div>
                                                        <div class="chat-room-details">
                                                            {{myroom.participants || 0}}/ 30 participants • 
                                                            <template v-if="myroom.isPublic == 0">
                                                                <ion-icon name="lock-closed-outline"></ion-icon> Private ({{myroom.randomPass}})
                                                            </template>
                                                            <template v-else>
                                                                <ion-icon name="lock-open-outline"></ion-icon> Public
                                                            </template>
                                                        </div>
                                                    </div>
                                                </router-link>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <div class="col-12 chat-room-item">
                                                You haven't created a room yet.
                                            </div>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <div class="col-12 chat-room-item">
                                            <div class="d-flex justify-content-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" style="margin-top: 10px;">
                        <div class="card">
                            <div class="card-header container-fluid">
                                <div class="row">
                                    <div class="col-12" style="vertical-align: middle !important;"><ion-icon name="log-in-outline"></ion-icon> Joined Rooms</div>
                                </div>
                            </div>

                            <div class="container-fluid">
                                <div class="row">
                                    <template v-if="loading.activeRoomLoaded">
                                        <template v-if="data.activeRooms.length > 0">
                                            <template v-for="(room, index) in data.activeRooms">
                                                <router-link v-if="room.joined > 0" class="col-12 chat-room-item" :to="'/r/'+room.id+'/'+room.roomName+'/'+room.isPublic" :key="'ROUTER'+index">
                                                    <div class="" :key="index">
                                                        <div class="chat-room-title">
                                                            <strong>{{room.roomName}}</strong> 
                                                            <template v-if="room.joined > 0">
                                                                ( Joined )
                                                            </template>
                                                        </div>
                                                        <div class="chat-room-details">
                                                            {{room.participants || 0}} / 30 participants • 
                                                            <template v-if="room.isPublic == 0">
                                                                <ion-icon name="lock-closed-outline"></ion-icon> Private
                                                            </template>
                                                            <template v-else>
                                                                <ion-icon name="lock-open-outline"></ion-icon> Public
                                                            </template>
                                                        </div>
                                                        <div class="chat-room-details">
                                                            Hosted by <strong>{{room.userName}}</strong>
                                                        </div>
                                                    </div>
                                                </router-link>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <div class="col-12 chat-room-item">
                                                You didn't join any of the rooms yet.
                                            </div>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <div class="col-12 chat-room-item">
                                            <div class="d-flex justify-content-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-12" style="margin-top: 10px;">
                        <div class="card">
                           <div class="card-header container-fluid">
                                <div class="row">
                                    <div class="col-12" style="vertical-align: middle !important;"><ion-icon name="home-outline"></ion-icon> Active Rooms</div>
                                </div>
                            </div>

                            <div class="container-fluid">
                                <div class="row">
                                    <template v-if="loading.activeRoomLoaded">
                                        <template v-if="data.activeRooms.length > 0">
                                            <template v-for="(room, index) in data.activeRooms">
                                                <router-link v-if="room.joined < 1" class="col-12 chat-room-item" :to="'/r/'+room.id+'/'+room.roomName+'/'+room.isPublic" :key="'ROUTER'+index">
                                                    <div class="" :key="index">
                                                        <div class="chat-room-title">
                                                            <strong>{{room.roomName}}</strong> 
                                                            <template v-if="room.joined > 0">
                                                                ( Joined )
                                                            </template>
                                                        </div>
                                                        <div class="chat-room-details">
                                                            {{room.participants || 0}} / 30 participants • 
                                                            <template v-if="room.isPublic == 0">
                                                                <ion-icon name="lock-closed-outline"></ion-icon> Private
                                                            </template>
                                                            <template v-else>
                                                                <ion-icon name="lock-open-outline"></ion-icon> Public
                                                            </template>
                                                        </div>
                                                        <div class="chat-room-details">
                                                            Hosted by <strong>{{room.userName}}</strong>
                                                        </div>
                                                    </div>
                                                </router-link>
                                            </template>
                                        </template>
                                        <template v-else>
                                            <div class="col-12 chat-room-item">
                                                No active rooms yet.
                                            </div>
                                        </template>
                                    </template>
                                    <template v-else>
                                        <div class="col-12 chat-room-item">
                                            <div class="d-flex justify-content-center">
                                                <div class="spinner-border" role="status">
                                                    <span class="sr-only">Loading...</span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" style="margin-top: 10px;">
                        <div class="card">
                            <div class="card-body">
                                Having fun with this app? Why don't you buy me a coffe! 
                            </div>
                        </div>
                    </div>

                    <div class="col-12" style="margin-top: 10px;">
                        <div class="card">
                            <div class="card-body">
                                Let me know about your ideas, suggestions or improvements. Send me a slack message through email.
                            </div>
                        </div>
                    </div>
                </div>
               
            </div>
            <div class="col-md-9 container-fluid">
                <div class="row">
                    <div class="col-12">
                        <router-view></router-view>
                    </div>
                    <div class="col-12" style="margin-top: 10px;">
                        <div class="card">
                            <div class="card-body">
                                Developed & Maintained by <a href="https://github.com/reidsolon" target="_blank"><ion-icon name="logo-github"></ion-icon> Ray Anthony Solon.</a> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {pusher} from '../includes/pusher'
import {validate, makeid} from '../includes/validation'
import Modal from '../reusable/Modal'
export default {
    components: {
        Modal,
    },
    data() {
        return {
            
            data: {
                userData: Object,
                pusherVal: Object,
                isPrivate: false,
                name: '',
                availability: false,
                myRooms: [],
                activeRooms: [],
            },

            // loading
            loading: {
                creatingRoom: false,
                availableLoad: false,
                myRoomLoaded: false,
                activeRoomLoaded: false,
            },

            // validation 
            validation: {
                isValidated: false,
                isAvailable: false,
                isChecked: false,
                isValid: false,
                required: ''
            }
        }
    },
    methods: {
        getActiveRooms() {
            this.loading.activeRoomLoaded = false
            axios.get('/room/allRooms')
            .then( res => {
                if(res.status == 200) {
                    this.loading.activeRoomLoaded = true
                    if(res.data.status == 1) {
                        this.data.activeRooms = res.data.rows
                    } else {
                        console.log(res.data.message)
                    }
                } else {
                    console.error(res.status)
                }
            })
            .catch( err => {
                console.error(err)
            })
        },
        getUserRooms() {
            this.loading.myRoomLoaded = false
            axios.get('/room/userRooms')
            .then( res => {
                if(res.status == 200) {
                    this.loading.myRoomLoaded = true
                    if(res.data.status == 1) {
                        this.data.myRooms = res.data.rows
                    } else {
                        console.log(res.data.message)
                    }
                } else {
                    console.error(res.status)
                }
            })
            .catch( err => {
                console.error(err)
            })
        },
        createRoom() {
            this.loading.creatingRoom = true
            var val = validate('required')
            if(val) {
                const payload = {
                    name: this.data.name,
                    isPrivate: this.data.isPrivate,
                    randomPass: makeid(6)
                }
                axios.post('/room/create', payload)
                .then( res => {
                    this.loading.creatingRoom = false
                    if(res.status == 200) {
                        if(res.data.status == 1) {
                            this.getUserRooms()
                            document.querySelector("button[data-dismiss='modal']").click()
                            this.$toastr.s(res.data.message, "Success")
                        } else {
                            this.$toastr.e(res.data.message, "Failed")
                        }
                    } else {
                        this.$toastr.w(`Request failure with the status code of ${res.status}`, "Success Toast Title")
                    }
                })
                .catch( err => {
                    this.$toastr.e(err)
                    this.loading.creatingRoom = false
                })
            }
        },  
        availability() {
            this.loading.availableLoad = true
            var val = validate('required')
            if(val) {

                this.validation.isValidated = val.valid
                this.validation.required = val.required
                this.validation.isChecked = true

                if(val.valid) {
                    const payload = {
                        name: this.data.name
                    }
                    axios.put('/room/isExist', payload)
                    .then( res => {
                        if(res.status == 200) {
                            if(res.data.status == 1) {
                                this.loading.availableLoad = false
                                this.data.availability = true
                                this.isReady = true
                            } else {
                                this.loading.availableLoad = false
                                this.data.availability = false
                                this.isReady = false
                            }
                        } else {
                            this.$toastr.w(`Error with status code of ${res.status}`)
                        }
                    })
                    .catch( err => {
                        console.error(err)
                    })
                }
                
            }
        },
        _initPusher() {
            this.pusherVal = pusher

            // -- NEW ROOM LISTENER
            const newRoomListener = this.pusherVal.subscribe('all-users')
            newRoomListener.bind('new-room', (data) => {
                if(data.room.isPublic == 1) {
                    this.$toastr.i(`${data.user.name} added a new public room!`, `Room Name ${data.room.roomName}`)
                } else {
                    this.$toastr.i(`${data.user.name} added a new private room!`, `Room Name ${data.room.roomName}`)
                }
                this.getActiveRooms()
            })

            // -- JOINED ROOM LISTENER
            const joinedRoomListener = this.pusherVal.subscribe(`join-channel-${this.data.userData.id}`)
            joinedRoomListener.bind('room-joined', (data) => {
                this.$toastr.i(`${data.user.name} has joined your room!`, `Room ${data.channel[0].roomName}`)
                this.getUserRooms()
            })
            
        },
        _getCurrentUser() {
            axios.get('/user/getCurrentUser')
            .then( res => {
                if(res.status == 200) {
                    this.data.userData = res.data
                    this._initPusher()
                } else {
                    this.$toastr.w(`Error with status code of ${res.status}`, 'User Data')
                }
            })
            .catch( err => {

            })
        }
    },
    mounted() {
        this._getCurrentUser()
        this.getUserRooms()
        this.getActiveRooms()

        //welcome message
        this.$toastr.s('Hope you are having an amazing day!', 'Welcome to Live Chat Group')
    }

}
</script>
<style scoped>
.chat-room-details {
    color: rgb(134, 134, 236);
    font-size: 80%;
}
.err {color: red;}
.chat-room-item {padding: 15px !important; cursor: pointer;}
.chat-room-item:hover {background-color:rgb(230, 230, 235);}
</style>