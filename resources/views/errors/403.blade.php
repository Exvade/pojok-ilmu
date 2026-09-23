@extends('errors::layout')

@section('title', __('Forbidden'))
@section('code', '403')
@section('message', __('Akses Ditolak'))
@section('description', 'Maaf, Anda tidak memiliki izin (hak akses) untuk membuka halaman atau melakukan tindakan ini.')
