@extends('clinics.layout.app')

@section('title', $visit->patient_name . ' — ' . $visit->code)

@section('content')

    <x-workflow.header :visit="$visit"/>

    <x-workflow.step-bar
        :visit="$visit"
        :steps="$steps"
        :currentStep="$currentStep"
    />

    <div class="row g-3">

        {{-- Main Step Content --}}
        <div class="col-12 col-lg-9">
            <div class="card-emr">

                @include('clinics.workflow.steps.' . $currentStep, [
                    'visit'   => $visit,
                    'steps'   => $steps,
                    'stepIdx' => array_search($currentStep, array_column($steps, 'id')),
                ])

                <x-workflow.action-bar
                    :form-id="'stepForm'"
                    :skip-route="route('workflow.skip', [$visit->code, $currentStep])"
                    :prev-step-route="$prevStep ? route('workflow.step', [$visit->code, $prevStep]) : null"
                    :next-step-route="$nextStep ? route('workflow.step', [$visit->code, $nextStep]) : null"
                    :step-number="$stepNumber"
                    :total-steps="count($steps)"
                />

            </div>
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-3 d-none d-lg-block">
            @include('clinics.workflow.visit-summary', [
                'visit' => $visit,
                'steps' => $steps,
                'currentStep' => $currentStep
            ])
        </div>
    </div>

@endsection
