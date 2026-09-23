@extends('errors::layout')

@section('title', __('Server Error'))
@section('code', '500')
@section('message', __('Terjadi Kesalahan Internal'))
@section('description', 'Maaf, sistem kami sedang mengalami gangguan internal saat memproses permintaan Anda. Tim kami telah diberitahu dan akan segera memperbaikinya.')
