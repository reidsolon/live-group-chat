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
            <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
        <div class="card-footer" v-if="joined">
            <div class="input-group mb-3">
                <input type="text" class="form-control" :placeholder="`Say something to ${route.room_name} ...`">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button">Send</button>
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
        <div class="card-footer">
            Enjoy using this app? Buy me a coffee!
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
            }
        }
    },
    watch: {
        '$route' : 'setRoute'
    },
    methods: {
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
</style>