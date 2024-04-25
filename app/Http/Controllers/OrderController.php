<?php

namespace App\Http\Controllers;

use App\Models\ConcertCategory;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::select('id', 'customer_name', 'order_date', 'order_time', 'status', 'total_amount')->get();
        return response(['data' => $orders]);
    }

    public function show($id)
    {
        $order = Order::select('id', 'customer_name', 'order_date', 'order_time', 'status', 'total_amount', 'users_id')->findOrFail($id);

        return response(['data' => $order->loadMissing([
            'orderDetail:id,orders_id,concert_categories_id,qty,price',
            'orderDetail.concertCategory:id,concerts_id,ticket_categories_id',
            'orderDetail.concertCategory.concert:id,name,date,location,vendors_id,poster',
            'orderDetail.concertCategory.ticketCategory:id,name,price',
            'orderDetail.concertCategory.concert.vendor:id,name,address,contact',
            'login:id,name,email'
        ])]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|max:100',
            'qty' => 'required|integer|min:1', // Validasi untuk qty
            'categories' => 'required|array', // Memastikan categories ada dan merupakan array
            'categories.*' => 'exists:concert_categories,id', // Memastikan setiap kategori yang diberikan ada di database
        ]);

        try {
            DB::beginTransaction();

            $data = $request->only(['customer_name']); // Hanya mengambil data customer_name dari request
            $data['order_date'] = now()->toDateString(); // Menggunakan Carbon untuk mendapatkan tanggal saat ini
            $data['order_time'] = now()->toTimeString(); // Menggunakan Carbon untuk mendapatkan waktu saat ini
            $data['total_amount'] = 0; // Total amount ditetapkan ke 0, dapat dihitung kemudian jika diperlukan
            $data['status'] = 'ordered'; // Menentukan status secara internal
            $data['users_id'] = auth()->user()->id;

            $order = Order::create($data); // Membuat order baru

            // Menggunakan collection untuk mengiterasi melalui kategori
            collect($request->categories)->each(function ($categoryId) use ($order, $request) {
                $concert = ConcertCategory::findOrFail($categoryId); // Mendapatkan kategori konser berdasarkan ID
                $ticket = TicketCategory::findOrFail($concert->ticket_categories_id); // Mendapatkan kategori tiket berdasarkan kategori konser

                $qty = $request->input('qty', 1); // Mengambil nilai qty dari inputan pengguna, default 1 jika tidak ada

                OrderDetail::create([
                    'orders_id' => $order->id,
                    'concerts_id' => $concert->concerts_id,
                    'concert_categories_id' => $concert->id,
                    'qty' => $qty,
                    'price' => $ticket->price
                ]);

                // Menambahkan total amount dari kategori ke total amount order
                $order->total_amount += $ticket->price * $qty;
            });

            $order->save(); // Menyimpan perubahan pada total amount pada order

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json(['error' => $th->getMessage()], 500); // Mengembalikan pesan kesalahan
        }

        return response()->json(['data' => $order], 201); // Mengembalikan data order dengan status 201 Created
    }

    public function setAsPaid($id)
    {
        /*
        Catatan Penting
        // Fungsi ini sangat krusial dalam transaksi karena siapa saja bisa mengakses end point nya asal sudah login
        // perlu dilakukan validasi tambahan nantinya setelah menggunakan payment gateway
        */

        $order = Order::findOrFail($id);

        if ($order->status !== "ordered") {
            return response('Pesanan ini tidak bisa dinyatakan sebagai terbayar atau Status pesanan sudah terbayar', 403);
        }

        try {
            DB::beginTransaction();
            $order->status = "paid";
            $order->save();

            DB::commit();

            return response()->json(['data' => $order]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Status pesanan gagal diubah '], 500);
        }
    }
}
