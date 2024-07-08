<?php
use function Laravel\Folio\name;
use function Livewire\Volt\{state, computed, rules, on};
use App\Models\Car;
use App\Models\Rental;
use App\Models\Transaction;

name('transactions.create');

state([
    'cars' => fn() => Car::all(),
    'price_car' => 0,
    'car_id' => '',
    'duration' => 1,
    'with_driver' => false,
    'car' => '',
    'user_id',
    'description',
    'rent_date',
    'total',
]);

$getPriceCar = computed(function () {
    $car = Car::find($this->car_id);
    return $car ? $car->price : 0;
});

$updatedCarId = function ($value) {
    $car = Car::find($value);
    $this->price_car = $car ? $car->price : null;
};

// $updatedCar = function ($value) {
//     $car = Car::find($value);
//     $this->car = $car ? $car : null;
// };

rules([
    'user_id' => ['required', 'exists:users,id'],
    'car_id' => ['required', 'exists:cars,id'],
    'rent_date' => ['required', 'date', 'after:today'],
    'duration' => ['required', 'integer', 'min:1', 'max:30'],
    'with_driver' => ['required', 'boolean'],
]);

on([
    'toggleCondition' => function () {
        $this->condition = $this->condition;
    },
]);

$increment = fn() => $this->duration++;
$decrement = fn() => $this->duration--;

$calculateTotal = function () {
    $total = 0;

    if ($this->with_driver == 1) {
        $driver = 200000;
    } else {
        $driver = 0;
    }

    $subTotal = $this->price_car * $this->duration;
    $total = $subTotal + $driver;
    return $total;
};

$rentCar = function () {
    if (Auth::check()) {
        $validate = $this->validate();

        $additionalData = [
            'price_car' => $this->price_car,
            'price_driver' => $this->with_driver ? 200000 : 0,
            'total' => $this->calculateTotal(),
        ];

        Transaction::create(array_merge($validate, $additionalData));
        $this->redirectRoute('succesfully');
    } else {
        $this->redirect('/login');
    }
};

?>
<x-admin-layout>
    <x-slot name="title">Data Transaksi Rental</x-slot>

    @volt
        <div>
            <x-alert on="status">
            </x-alert>
            <form wire:submit.prevent='rentCar' method="post">

                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="car_id" class="form-label">Mobil</label>
                            <div class="input-group">
                                <select class="form-select w-75" wire:model.live="car_id" id="car_id">
                                    <option selected>Select one</option>
                                    @foreach ($cars as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}</option>
                                    @endforeach
                                </select>
                                <input type="text" wire:model.live='price_car' class="form-control" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="rent_date" class="form-label">Tanggal Rental</label>
                            <input type="date" class="form-control @error('rent_date') is-invalid @enderror"
                                wire:model="rent_date" value="{{ today() }}" id="rent_date" aria-describedby="helpId"
                                placeholder="rent_date" />
                            @error('rent_date')
                                <small id="helpId" class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="duration" class="form-label">Durasi</label>
                            <div class="input-group input-group-sm justify-content-center">
                                <input type="number" class="form-control @error('duration') is-invalid @enderror"
                                    wire:model="duration" id="duration" aria-describedby="helpId" placeholder="duration"
                                    disabled />
                                <button type="button" class="btn btn-body btn-sm border rounded-start-pill"
                                    wire:loading.attr='disabled' wire:click="decrement">
                                    <i class="fa-solid fa-minus"></i>
                                </button>
                                <button type="button" class="btn btn-body btn-sm border rounded-end-circle"
                                    wire:loading.attr='disabled' wire:click="increment">
                                    <i class="fa-solid fa-plus"></i>
                                </button>

                            </div>
                            @error('duration')
                                <small id="helpId" class="form-text text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input wire:model.live='with_driver' class="form-check-input" type="checkbox" value=""
                                id="with_driver" {{ $with_driver == 0 ? '' : 'checked' }}>
                            <label class="form-check-label" for="with_driver">
                                <strong>
                                    Gunakan layanan pengemudi profesional
                                </strong>
                                <p>
                                    Jasa pengemudi profesional ini akan memastikan kamu tiba di tujuan dengan aman
                                    dan
                                    nyaman.
                                </p>
                                <p class="fw-bold text-primary">
                                    Rp. 200.000
                                </p>
                            </label>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="row">
                                <dt class="col-5 mb-2">
                                    Harga Mobil x Durasi
                                </dt>
                                <dd class="col-7 mb-2 text-end">
                                    {{ 'Rp.' . Number::format($price_car * $duration, locale: 'id') }}
                                    <input type="hidden" wire:model='price_car' value="{{ $price_car }}">
                                </dd>
                                <dt class="col-5 mb-2">
                                    Layanan Pengemudi
                                </dt>
                                <dd class="col-7 mb-2 text-end">
                                    {{ 'Rp.' . Number::format($with_driver == 1 ? 200000 : 0, locale: 'id') }}

                                </dd>
                                <dt class="col-5 mb-2">
                                    Total
                                </dt>
                                <dd class="col-7 mb-2 text-end">
                                    {{ 'Rp.' . Number::format($this->calculateTotal(), locale: 'id') }}
                                    <input type="hidden" wire:model='total' value="{{ $this->calculateTotal() }}">

                                </dd>

                                <hr>
                            </div>
                            <div class="d-grid mb-3 ">
                                <button type="submit" class="btn btn-primary rounded" href="#"
                                    role="button">Submit</button>
                            </div>
                        </div>
                    </div>

            </form>
        </div>
    @endvolt

</x-admin-layout>
