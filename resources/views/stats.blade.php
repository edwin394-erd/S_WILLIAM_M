@extends('layouts.app')
@section('title', 'estadisticas administrativas')
@section('content')
<div class="flex gap-6">
    <div>

       <x-leads-chart 
            value="3.4k" 
            label="Leads generated per week" 
            percentage="42.5" 
            money-spent="$3,232" 
            conversion="1.2%" 
            :series="[
                ['name' => 'Organic', 'data' => [44, 55, 57, 56, 61, 58, 63]],
                ['name' => 'Ads', 'data' => [76, 85, 101, 98, 87, 105, 91]]
            ]" 
            :categories="['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" 
        />
    </div>

    <div>

        <x-leads-chart 
            value="3.4k" 
            label="Leads generated per week" 
            percentage="42.5" 
            money-spent="$3,232" 
            conversion="1.2%" 
            :series="[
                ['name' => 'Organic', 'data' => [44, 55, 57, 56, 61, 58, 63]],
                ['name' => 'Ads', 'data' => [76, 85, 101, 98, 87, 105, 91]]
            ]" 
            :categories="['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']" 
        />
    </div>
</div>

@endsection
