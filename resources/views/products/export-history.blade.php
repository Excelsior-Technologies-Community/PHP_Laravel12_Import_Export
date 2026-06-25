@extends('layouts.app')


@section('content')


<div class="card">

    <div class="card-header">

        <h5>Export History</h5>

    </div>


    <div class="card-body">


        <table class="table table-bordered">


            <tr>

                <th>File</th>
                <th>Format</th>
                <th>Total Products</th>
                <th>Date</th>


            </tr>


            @foreach($histories as $history)


            <tr>

                <td>{{$history->file_name}}</td>

                <td>{{$history->format}}</td>

                <td>{{$history->total_rows}}</td>

                <td>{{$history->created_at}}</td>


            </tr>


            @endforeach


        </table>


        {{$histories->links()}}


    </div>

</div>


@endsection