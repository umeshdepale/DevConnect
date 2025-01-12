# DevConnect

DevConnect is a platform for connecting clients with developers for one-on-one video calls to discuss and collaborate on projects. The system includes real-time booking notifications, video call integration, and automatic payment handling based on the duration of calls.

---

## Features

- **User Roles**:
  - **Clients**: Can book developers for video calls and manage bookings.
  - **Developers**: Receive notifications for bookings, accept/reject requests, and join video calls.
- **Video Call Integration**: Built using [Jitsi Meet](https://jitsi.org/), enabling seamless video conferencing.
- **Payment System**:
  - Clients are charged per minute based on the developer's rate.
  - Payments are automatically deducted from the client's wallet and credited to the developer's wallet.
- **Notifications**:
  - Real-time notifications for booking status (pending, accepted, rejected).
  - Both clients and developers receive appropriate alerts.
- **Meeting Logs**:
  - Tracks meeting start and end times, calculates duration, and logs payments.
- **Admin Dashboard** (Optional):
  - Monitor user activities, bookings, and transactions.

---

## Tech Stack

- **Frontend**: HTML, Tailwind CSS, JavaScript, jQuery
- **Backend**: PHP, MySQL
- **Video Call Integration**: Jitsi Meet
- **Wallet Integration**: Supports manual top-ups and can be integrated with payment gateways (e.g., Binance Pay, Metamask).

---
