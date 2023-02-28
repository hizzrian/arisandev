@extends('layouts.app')

@section('title', __('meeting.detail'))

@section('content')
<h1 class="page-header">
    <div class="pull-right">
        {{ link_to_route(
            'meetings.show',
            __('meeting.set_winner'),
            [$meeting, 'action' => 'set-winner'],
            ['id' => 'set-winner', 'class' => 'btn btn-success']
        ) }}
        {{ link_to_route(
            'meetings.show',
            __('meeting.edit', ['number' => $meeting->number]),
            [$meeting, 'action' => 'edit-meeting'],
            ['id' => 'edit-meeting-'.$meeting->number, 'class' => 'btn btn-warning']
        ) }}
        {{ link_to_route('groups.meetings.index', __('meeting.back_to_index'), [$group], ['class' => 'btn btn-default']) }}
    </div>
    {{ __('meeting.number') }} {{ $meeting->number }}
    <small>{{ $group->name }}</small>
</h1>

<div class="row">
    <div class="col-md-4">
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{{ __('meeting.detail') }}</h3></div>
            <table class="table table-condensed">
                <tbody>
                    <tr><td class="col-md-4">{{ __('group.group') }}</td><td>{{ $group->nameLink() }}</td></tr>
                    <tr><td>{{ __('meeting.number') }}</td><td>{{ $meeting->number }}</td></tr>
                    <tr><td>{{ __('meeting.date') }}</td><td>{{ $meeting->date }}</td></tr>
                    <tr><td>{{ __('meeting.place') }}</td><td>{{ $meeting->place }}</td></tr>
                    <tr><td>{{ __('meeting.creator') }}</td><td>{{ $meeting->creator->name }}</td></tr>
                    <tr><td>{{ __('meeting.notes') }}</td><td>{{ $meeting->notes }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="col-md-8">
        @include ('meetings.partials.stats')
        <div class="panel panel-default">
            <div class="panel-heading"><h3 class="panel-title">{{ __('meeting.payments') }}</h3></div>
            <table class="table table-condensed">
                <thead>
                    <tr>
                        <th style="width: 5%" class="text-center">{{ __('app.table_no') }}</th>
                        <th style="width: 25%">{{ __('user.name') }}</th>
                        <th style="width: 10%" class="text-center">{{ __('app.status') }}</th>
                        <th style="width: 17%" class="text-center">{{ __('payment.amount') }}</th>
                        <th style="width: 13%" class="text-center">{{ __('payment.date') }}</th>
                        <th style="width: 20%">{{ __('payment.to') }}</th>
                        <th style="width: 10%" class="text-center">{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($members as $key => $member)
                    @php
                        $membershipId = $member->pivot->id;
                        $payment = $payments->filter(function ($payment) use ($membershipId, $meeting) {
                            return $payment->membership_id == $membershipId
                            && $payment->meeting_id == $meeting->id;
                        })->first();
                    @endphp
                    {{ Form::open(['route' => ['meetings.payment-entry', $meeting]]) }}
                    {{ Form::hidden('membership_id', $membershipId) }}
                    <tr>
                        <td class="text-center">{{ 1 + $key }}</td>
                        <td>{{ $member->name }}</td>
                        <td class="text-center">
                            @if ($meeting->winner_id == $membershipId)
                                <span class="label label-primary">{{ __('meeting.winner') }}</span>
                            @elseif ($payment)
                                <span class="label label-success">{{ __('payment.done') }}</span>
                            @else
                                <span class="label label-default">{{ __('payment.not_yet') }}</span>
                            @endif
                        </td>
                        <td class="text-right">
                            {!! FormField::price(
                                'amount',
                                [
                                    'value' => optional($payment)->amount, 'label' => false,
                                    'required' => true, 'currency' => $group->currency
                                ]
                            ) !!}
                        </td>
                        <td class="text-center">
                            {!! FormField::text(
                                'date',
                                [
                                    'value' => $payment ? $payment->date : $meeting->date,
                                    'label' => false,
                                    'required' => true,
                                    'class' => 'date-select',
                                ]
                            ) !!}
                        </td>
                        <td>
                            {!! FormField::select(
                                'payment_receiver_id',
                                $members->pluck('name', 'id'),
                                [
                                    'value' => optional($payment)->payment_receiver_id,
                                    'label' => false,
                                    'required' => true
                                ]
                            ) !!}
                        </td>
                        <td class="text-center">
                            {{ Form::submit(
                                __('app.update'),
                                [
                                    'id' => 'payment-entry-'.$membershipId,
                                    'class' => 'btn btn-success btn-xs',
                                    'title' => __('payment.update'),
                                ]
                            ) }}
                        </td>
                    </tr>
                    {{ Form::close() }}
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-center">{{ __('app.total') }}</th>
                        <th class="text-right">{{ $group->currency }} {{ formatNo($payments->sum('amount')) }}</th>
                        <th colspan="3">&nbsp;</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div id="wheel" class="col-md-6">
        <div class="panel panel-default">
            <canvas id="canvas" width="500" height="500"></canvas>
           
            <button id="spin" class="btn btn-primary btn-sml">{{ __('spin') }}</button>
        </div>
    </div>
</div>

@includeWhen(request('action') == 'edit-meeting', 'meetings.partials.edit-meeting')
@includeWhen (request('action') == 'set-winner', 'meetings.partials.set-winner')
@endsection

@section('styles')
    {{ Html::style(url('css/plugins/jquery.datetimepicker.css')) }}
@endsection

@push('scripts')
    {{ Html::script(url('js/plugins/jquery.datetimepicker.js')) }}
<script>
(function() {
    $('#meetingModal').modal({
        show: true,
        backdrop: 'static',
    });
    $('.date-select').datetimepicker({
        timepicker:false,
        format:'Y-m-d',
        closeOnDateSelect: true,
        scrollInput: false
    });
})();

 // get data member from group where select group

 var group = document.getElementById('group');
        
        @php
            $membership = App\Membership::leftJoin('users', 'users.id', '=', 'group_members.user_id')
                ->where('group_id', '=', $group->id)
                ->select('users.name')
                ->get();
        @endphp
            // push data $membership to options
            
        var options = [];
        @foreach ($membership as $member)
            options.push("{{ $member->name }}");
        @endforeach
        
        
        var startAngle = 0;
        var arc = Math.PI / (options.length / 2);
        var spinTimeout = null;

        var spinArcStart = 10;
        var spinTime = 0;
        var spinTimeTotal = 0;

        var ctx;

        document.getElementById("spin").addEventListener("click", spin);

        function byte2Hex(n) {
            var nybHexString = "0123456789ABCDEF";
            return String(nybHexString.substr((n >> 4) & 0x0F,1)) + nybHexString.substr(n & 0x0F,1);
        }

        function RGB2Color(r,g,b) {
            return '#' + byte2Hex(r) + byte2Hex(g) + byte2Hex(b);
        }

        function getColor(item, maxitem) {
            var phase = 0;
            var center = 128;
            var width = 127;
            var frequency = Math.PI*2/maxitem;
            
            red   = Math.sin(frequency*item+2+phase) * width + center;
            green = Math.sin(frequency*item+0+phase) * width + center;
            blue  = Math.sin(frequency*item+4+phase) * width + center;
            
            return RGB2Color(red,green,blue);
        }

        function drawRouletteWheel() {
            var canvas = document.getElementById("canvas");
            if (canvas.getContext) {
                var outsideRadius = 200;
                var textRadius = 160;
                var insideRadius = 125;

                ctx = canvas.getContext("2d");
                ctx.clearRect(0,0,500,500);

                ctx.strokeStyle = "black";
                ctx.lineWidth = 2;

                ctx.font = 'bold 12px Helvetica, Arial';

                for(var i = 0; i < options.length; i++) {
                var angle = startAngle + i * arc;
                //ctx.fillStyle = colors[i];
                ctx.fillStyle = getColor(i, options.length);

                ctx.beginPath();
                ctx.arc(250, 250, outsideRadius, angle, angle + arc, false);
                ctx.arc(250, 250, insideRadius, angle + arc, angle, true);
                ctx.stroke();
                ctx.fill();

                ctx.save();
                ctx.shadowOffsetX = -1;
                ctx.shadowOffsetY = -1;
                ctx.shadowBlur    = 0;
                ctx.shadowColor   = "rgb(220,220,220)";
                ctx.fillStyle = "black";
                ctx.translate(250 + Math.cos(angle + arc / 2) * textRadius, 
                                250 + Math.sin(angle + arc / 2) * textRadius);
                ctx.rotate(angle + arc / 2 + Math.PI / 2);
                var text = options[i];
                ctx.fillText(text, -ctx.measureText(text).width / 2, 0);
                ctx.restore();
                } 

                //Arrow
                ctx.fillStyle = "black";
                ctx.beginPath();
                ctx.moveTo(250 - 4, 250 - (outsideRadius + 5));
                ctx.lineTo(250 + 4, 250 - (outsideRadius + 5));
                ctx.lineTo(250 + 4, 250 - (outsideRadius - 5));
                ctx.lineTo(250 + 9, 250 - (outsideRadius - 5));
                ctx.lineTo(250 + 0, 250 - (outsideRadius - 13));
                ctx.lineTo(250 - 9, 250 - (outsideRadius - 5));
                ctx.lineTo(250 - 4, 250 - (outsideRadius - 5));
                ctx.lineTo(250 - 4, 250 - (outsideRadius + 5));
                ctx.fill();
            }
        }

        function spin() {
            spinAngleStart = Math.random() * 10 + 10;
            spinTime = 0;
            spinTimeTotal = Math.random() * 3 + 4 * 1000;
            rotateWheel();
        }

        function rotateWheel() {
            spinTime += 30;
            if(spinTime >= spinTimeTotal) {
                stopRotateWheel();
                return;
            }
            var spinAngle = spinAngleStart - easeOut(spinTime, 0, spinAngleStart, spinTimeTotal);
            startAngle += (spinAngle * Math.PI / 180);
            drawRouletteWheel();
            spinTimeout = setTimeout('rotateWheel()', 30);
        }

        function stopRotateWheel() {
         // stop when number 7
            clearTimeout(spinTimeout);
            var degrees = startAngle * 180 / Math.PI + 90;
            var arcd = arc * 180 / Math.PI;
            var index = Math.floor((360 - degrees % 360) / arcd);
            ctx.save();
            ctx.font = 'bold 30px Helvetica, Arial';
            var text = options[index]
            ctx.fillText(text, 250 - ctx.measureText(text).width / 2, 250 + 10);
            ctx.restore();
        }

        function easeOut(t, b, c, d) {
            var ts = (t/=d)*t;
            var tc = ts*t;
            return b+c*(tc + -3*ts + 3*t);
        }

        drawRouletteWheel();
</script>
@endpush
