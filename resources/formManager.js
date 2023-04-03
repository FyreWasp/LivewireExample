document.addEventListener('alpine:init', () => {
    /**
     * Object to manage data contained within a slider form.
     *
     * @param string cancelMethod name of the method on the wire to call when clicking the cancel button on a slider
     */
    Alpine.data('formManager', (orderClean, cancelMethod = 'cancelChanges') => ({
        /**
         * @var string Name of the slider form we are acting on.
         */
        triggerName: '',

        /**
         * @var bool Is a message currently being sent via the wire.
         */
        messageSending: false,

        /**
         * @var bool whether a confirmation is allowed when canceling the form
         */
        orderIsClean: orderClean,

        /**
         * @var string cancelMethod name of the method on the wire to call when clicking the cancel button on a slider
         */
        cancelMethod: cancelMethod,

        /**
         * Init hooks to track wire status.
         *
         * @return void
         */
        init() {
            Livewire.hook('message.sent', () => this.messageStart());
            Livewire.hook('message.processed', () => this.messageSent());
        },

        /**
         * close/Cancel modal form:
         * If the order is clean call the cancelConfirmed function.
         * If the order is dirty popup the confirmationDialog to make sure the user is aware of the data loss.
         *
         * @param string triggerName Name of the slider form we are acting on.
         *
         * @return void
         */
        async cancelForm(triggerName) {
            var orderIsClean = true;

            this.triggerName = triggerName;

            await this.waitForWire();

            if (this.orderIsClean) {
                this.cancelConfirmed();
            } else {
                this.$store.confirmationDialog.show();
            }
        },

        /**
         * Cancel the changes.
         *
         * Hide the slider form and hide the confirmationDialog if it is open.
         *
         * @return void
         */
        cancelConfirmed() {
            this.$wire[this.cancelMethod]();

            this.$store.slider.hide(this.triggerName);
            this.$store.confirmationDialog.hide();
        },

        /**
         * Check the status of the wire. Wait until its ready.
         *
         * @return void
         */
        async waitForWire() {
            while (this.messageSending) {
                await new Promise(resolve => setTimeout(resolve, 10));
            }
        },

        /**
         * Called by the livewire hook to flag that a message has started to send.
         *
         * @return void
         */
        messageStart() {
            this.messageSending = true;
        },

        /**
         * Called by the livewire hook when the message is done sending.
         *
         * @return void
         */
        messageSent() {
            this.messageSending = false;
        },
    }));
});
