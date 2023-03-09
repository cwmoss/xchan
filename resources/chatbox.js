/*
jquery original:
https://codepen.io/thatkookooguy/pen/VPJpaW
*/

function Chatbox(props) {
    return {
        $template: '#chatbox-template',
        title: props.title,
        enter: true,
        expand: false,
        open() {
            this.expand = true
        },
        close(){
            this.expand = false
        },
        messages: [
            {who:'other', msg:'asdasdasasdasdasasdasdasasdasdasasdasdasasdasdasasdasdas'},
            {who:'other', msg:'Are we dogs??? 🐶'},
            {who:'self', msg:'no... we\'re human'},
            {who:'other', msg:'are you sure???'},
            {who:'self', msg:'yes.... -___-'},
            {who:'other', msg:'if we\'re not dogs.... we might be monkeys 🐵'},
            {who:'self', msg:'i hate you'},
            {who:'other', msg:'don\'t be so negative! here\'s a banana 🍌'},
            {who:'self', msg:'......... -___-'}
        ],
        send_message(){
            console.log("send message", this.$refs.msg_input.innerHTML)
            var newMessage = this.$refs.msg_input.innerHTML.replace(/\<div\>|\<br.*?\>/ig, '\n').
                replace(/\<\/div\>/g, '').trim().
                replace(/\n/g, '<br>')

            if (!newMessage) return
            this.messages.push({who:'self', msg:newMessage})
            this.$refs.msg_input.innerHTML=""
            var box = this.$refs.messagebox
            console.log(this)
            window.setTimeout(()=>{
          //      box.scrollTop = box.scrollHeight
            }, 100)

           /* nextTick().then(()=>{
                box.scrollTop = box.scrollHeight
            })*/
        },
        mounted(){
            console.log("chat mounted", window.ws)
            ws.onmessage = m => {
                this.messages.push({who:'other', msg:m.data})
            }
        }
    }
}

