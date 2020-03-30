// -------------------------
// LIVE GROUP CHAT - PUSHER CONFIG 
// - RAY ANTHONY SOLON
// ------------------------

Pusher.logToConsole = true //console debugging
export const pusher = new Pusher('35176be266592e80efd7', {
    cluster: 'ap1',
    forceTLS: true
});

//--END CONFIG--------------