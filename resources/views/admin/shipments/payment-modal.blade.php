<!-- Payment Modal -->
<div id="paymentModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-xl font-bold text-gray-900">Bayar COD</h3>
            <button onclick="closePaymentModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        
        <form id="paymentForm" method="POST" class="p-6">
            @csrf
            <input type="hidden" id="paymentShipmentId" name="shipment_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Resi</label>
                <input type="text" id="paymentResiNumber" readonly 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah COD</label>
                <input type="text" id="paymentAmount" readonly 
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-3">Metode Pembayaran</label>
                <div class="space-y-3">
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#F4C430] transition-colors">
                        <input type="radio" name="payment_method" value="qris" class="mr-3 text-[#F4C430] focus:ring-[#F4C430]" required>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">QRIS</div>
                            <div class="text-sm text-gray-600">Bayar dengan QRIS (Midtrans)</div>
                        </div>
                    </label>
                    
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-[#F4C430] transition-colors">
                        <input type="radio" name="payment_method" value="cash" class="mr-3 text-[#F4C430] focus:ring-[#F4C430]" required>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">Cash</div>
                            <div class="text-sm text-gray-600">Bayar tunai langsung</div>
                        </div>
                    </label>
                </div>
            </div>
            
            <div class="flex gap-3">
                <button type="button" onclick="closePaymentModal()" 
                    class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="submit" 
                    class="flex-1 px-4 py-2 bg-[#F4C430] text-white rounded-lg font-semibold hover:bg-[#E6B020] transition-colors">
                    Bayar
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openPaymentModal(shipmentId, resiNumber, amount) {
        document.getElementById('paymentShipmentId').value = shipmentId;
        document.getElementById('paymentResiNumber').value = resiNumber;
        document.getElementById('paymentAmount').value = 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        
        // Set form action
        const form = document.getElementById('paymentForm');
        const paymentMethod = form.querySelector('input[name="payment_method"]:checked');
        if (paymentMethod) {
            paymentMethod.checked = false;
        }
        
        // Update form action based on payment method
        const formSubmitHandler = function(e) {
            e.preventDefault();
            const selectedMethod = form.querySelector('input[name="payment_method"]:checked');
            if (!selectedMethod) {
                alert('Pilih metode pembayaran terlebih dahulu');
                return;
            }
            
            if (selectedMethod.value === 'qris') {
                form.action = '{{ url("/courier/shipments") }}/' + shipmentId + '/payment/qris';
            } else if (selectedMethod.value === 'cash') {
                form.action = '{{ url("/courier/shipments") }}/' + shipmentId + '/payment/cash';
            }
            
            form.submit();
        };
        
        // Remove existing listener if any
        form.removeEventListener('submit', formSubmitHandler);
        form.addEventListener('submit', formSubmitHandler);
        
        document.getElementById('paymentModal').classList.remove('hidden');
    }
    
    function closePaymentModal() {
        document.getElementById('paymentModal').classList.add('hidden');
    }
    
    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('paymentModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closePaymentModal();
                }
            });
        }
    });
</script>

