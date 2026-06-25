@extends('layouts.app')


@section('content')


<div class="card">

    <div class="card-header">

        <h5>Import History</h5>

    </div>


    <div class="card-body">


        <table class="table table-bordered">


            <tr>

                <th>File</th>
                <th>Total</th>
                <th>Success</th>
                <th>Failed</th>
                <th>Date</th>

            </tr>



            @foreach($histories as $history)

            <tr>

                <td>{{$history->file_name}}</td>

                <td>{{$history->total_rows}}</td>

                <td>{{$history->success_rows}}</td>

                <td>{{$history->failed_rows}}</td>

                <td>{{$history->created_at}}</td>


            </tr>


            @endforeach


        </table>


        {{$histories->links()}}


    </div>

</div>


@endsection