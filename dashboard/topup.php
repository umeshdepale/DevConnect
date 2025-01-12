<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top-Up with MetaMask</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="max-w-md mx-auto mt-10 bg-white p-6 rounded-lg shadow">
        <h1 class="text-xl font-bold mb-4">Top-Up with Crypto</h1>
        <form id="topUpForm">
            <label class="block mb-2 text-gray-700">Amount (ETH)</label>
            <input type="number" id="amount" name="amount" class="w-full px-4 py-2 border rounded-lg" required>
            <button type="button" id="connectWallet" class="mt-4 bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 w-full">
                Connect Wallet
            </button>
            <button type="button" id="payButton" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 w-full hidden">
                Pay with MetaMask
            </button>
        </form>
    </div>

    <script>
        let userAccount;

        // Check if MetaMask is installed
        if (typeof window.ethereum === 'undefined') {
            alert("MetaMask is not installed. Please install it to use this feature.");
        }

        // Connect MetaMask Wallet
        document.getElementById("connectWallet").addEventListener("click", async function () {
            try {
                const accounts = await ethereum.request({ method: 'eth_requestAccounts' });
                userAccount = accounts[0];
                alert("Wallet Connected: " + userAccount);
                document.getElementById("payButton").classList.remove("hidden");
            } catch (error) {
                console.error("Error connecting wallet:", error);
                alert("Failed to connect wallet. Please try again.");
            }
        });

        // Pay with MetaMask
        document.getElementById("payButton").addEventListener("click", async function () {
            const amount = document.getElementById("amount").value;

            if (!amount || isNaN(amount) || amount <= 0) {
                alert("Please enter a valid amount.");
                return;
            }

            const valueInWei = (amount * 10 ** 18).toString(); // Convert ETH to Wei

            try {
                const transactionParameters = {
                    to: '0x38b32FeafF9dc0929e42fB01513a2e1178F47D57', // Replace with your contract address
                    from: userAccount,
                    value: valueInWei,
                };

                const txHash = await ethereum.request({
                    method: 'eth_sendTransaction',
                    params: [transactionParameters],
                });

                alert("Transaction submitted! Hash: " + txHash);
                // You can also call your backend to verify the transaction
            } catch (error) {
                console.error("Error sending transaction:", error);
                alert("Payment failed. Please try again.");
            }
        });
    </script>
</body>
</html>
