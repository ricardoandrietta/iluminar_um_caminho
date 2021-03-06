@extends('layouts.app')

@section('container')

    <div class="ui attached message">
        <div class="header">{{ $campaign->name }}</div>
        <p>{{ $campaign->description }}</p>
    </div>
    <div class="ui blue attached segment showLoading">
        <form action="{{route('checkout_process')}}" method="post" id="form_checkout">
            @csrf
            @method('post')
            <div class="ui form">
                <div class="ui grid">
                    <!-- Vaor da doação -->
                    <div class="three column row">
                        <div class="four wide column">&nbsp;</div>
                        <div class="twelve wide column">
                            <div class="field three wide">
                                <div class="required field">
                                    <label for="donationAmount">Valor da Doação</label>
                                    <input type="text" id="donationAmount" name="donationAmount"
                                           data-inputmask="'mask': '9{1,},9{2}', 'placeholder': ''">
                                </div>
                            </div>
                            <small>Valor sugerido R$ {{number_format($campaign->suggested_donation, 2, ',', '.')}}</small>
                        </div>
                    </div>

                    <!-- Dados do Cartão -->
                    <div class="three column row">
                        <div class="four wide column">&nbsp;</div>
                        <div class="eight wide column">
                            <div class="three fields">
                                <div class="ten wide required field">
                                    <label for="cardNumber">Número do cartão</label>
                                    <input type="text" id="cardNumber" data-inputmask="'mask': '9999 9999 9999 9999', 'placeholder': ''">
                                </div>
                                <div class="three wide required field">
                                    <label for="cvv">cvv</label>
                                    <input type="text" id="cvv">
                                </div>
                                <div class="three wide required field">
                                    <label for="expiration">Expira</label>
                                    <input type="text" id="expiration" data-inputmask="'mask': '99/99', 'placeholder': ''"
                                           placeholder="mm/aa">
                                </div>
                            </div>
                        </div>
                        <div class="four wide column">&nbsp;</div>
                    </div>

                    <!-- Dados do Proprietário do Cartão -->
                    <div class="three column row">
                        <div class="four wide column">&nbsp;</div>
                        <div class="eight wide column">
                            <div class="two fields">
                                <div class="ten wide required field">
                                    <label for="creditCardHolderName">Nome completo</label>
                                    <input type="text" id="creditCardHolderName" name="creditCardHolderName">
                                </div>
                                <div class="six wide required field">
                                    <label for="creditCardHolderCPF">CPF</label>
                                    <input type="text" id="creditCardHolderCPF" name="creditCardHolderCPF"
                                           data-inputmask="'mask': '999.999.999-99', 'placeholder': ''">
                                </div>

                            </div>
                        </div>
                        <div class="four wide column">&nbsp;</div>
                    </div>
                </div>
            </div>
            <div class="ui divider"></div>
            <div class="ui column grid">
                <div class="row">
                    <div class="column right aligned">
                        <button type="button" id="btnDoar" class="ui button addLoading blue">Doar <i class="smile outline right aligned icon"></i></button>
                    </div>
                </div>
            </div>
            <input type="hidden" name="campaign_id" id="campaignId" value="{{$campaign->id}}">
            <input type="hidden" name="campaign_name" value="{{$campaign->name}}">
            <input type="hidden" name="creditCardToken" id="creditCardToken">
            <input type="hidden" name="installmentValue" id="installmentValue" value="1">
            <input type="hidden" name="installmentQuantity" id="installmentQuantity" value="1">
        </form>
    </div>
    <div class="ui teal attached message hidden" id="steps">
        <div class="ui mini steps">
            <div class="active step" id="step_lock">
                <i class="lock icon"></i>
                <div class="content">
                    <div class="title">Passo 1 - Segurança</div>
                    <div class="description">Criptografando seus dados</div>
                </div>
            </div>
            <div class="disabled step" id="step_payment">
                <i class="payment icon"></i>
                <div class="content">
                    <div class="title">Passo 2 - Integração</div>
                    <div class="description">Recebendo sua doação através do Pag Seguro</div>
                </div>
            </div>
            <div class="disabled step" id="step_confirmation">
                <i class="info icon"></i>
                <div class="content">
                    <div class="title">Passo 3 - Finalizando</div>
                    <div class="description">Confirmando sua doação</div>
                </div>
            </div>
        </div>
    </div>
    <div class="ui attached message">
        <img src="{{ asset('images/payment_flags/mastercard.png') }}" alt="Mastercard">
        <img src="{{ asset('images/payment_flags/visa.png') }}" alt="Visa">
        <img src="{{ asset('images/payment_flags/elo.png') }}" alt="Alelo">
        <img src="{{ asset('images/payment_flags/diners.png') }}" alt="Diners Club">
        <img src="{{ asset('images/payment_flags/amex.png') }}" alt="American Express">
        <img src="{{ asset('images/payment_flags/hipercard.png') }}" alt="Hipercard">
    </div>
    <div class="ui bottom attached success message">
        <i class="info icon"></i>
            A doação será recebida através do PagSeguro. Haverá um desconto de 3,99% + R$ 0,40.
    </div>

@endsection
@section('js')

    {{--<script type="text/javascript" src="https://stc.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>--}}
    <script type="text/javascript" src="https://stc.sandbox.pagseguro.uol.com.br/pagseguro/api/v2/checkout/pagseguro.directpayment.js"></script>

    <script>

        $('#donationAmount').val('16,50');
        $('#cardNumber').val('4111111111111111');
        $('#cvv').val('123');
        $('#expiration').val('12/30');
        $('#creditCardHolderName').val('Comprador Teste');
        $('#creditCardHolderCPF').val('57822101250');

        PagSeguroDirectPayment.setSessionId('{{$session}}');

        // Passo 1 - Gerar o token do cartao de credito
        $('#btnDoar').on('click', function (e) {
            e.preventDefault();

            $('#step_lock').removeClass('disabled').addClass('active');
            $('#step_payment').removeClass('active').addClass('disabled');
            $('#step_confirmation').removeClass('active').addClass('disabled');
            $('#steps').removeClass('hidden');

            // Passo 1
            let fullCard = $('#cardNumber').val();
            let numCard = fullCard.split('_').join('').split(' ').join('');
            let expiration = $('#expiration').val().split('/');

            let params = {
                cardNumber: numCard,
                cvv: $('#cvv').val(),
                expirationMonth: expiration[0],
                expirationYear: '20' + expiration[1],
                success: function (response) {
                    executeCheckOut(response.card.token)
                },
                error: function (response) {
                    console.log(response);
                    if (response.error === true) {
                        for (let [key, value] of Object.entries(response.errors)) {
                            toastr.error(value, 'Erro com mo cartão de crédito:');
                        }
                    }
                    $(".showLoading").removeClass('loading');
                }
            };

            PagSeguroDirectPayment.createCardToken(params);
        });

        // Passo 2 - Executar o checkout com o token do cartão gerado pelo pag seguro
        function executeCheckOut(token) {

            $('#creditCardToken').val(token);

            let url = $('#form_checkout').attr('action');
            let data = $('#form_checkout').serialize();

            $('#step_lock').removeClass('active');
            $('#step_payment').removeClass('disabled').addClass('active');

            $.post(url, data).then(

                function (result) {
                    finishDonation(result);
                },
                function (error) {
                    let errorHTL = '';
                    if (error.status === 403) {
                        errorHTL += '<p>Você deve finalizar seu cadastro para poder efetuar uma doação.</p>';
                        errorHTL += '<a href="{{ route('user.update', ['id' => \Illuminate\Support\Facades\Auth::id()]) }}">Clique aqui para fazer a atualização.</a>';
                    } else if (error.status === 409) {
                        errorHTL += '<p>'+error.responseText+'</p>';
                    } else {
                        errorHTL += '<ul>';

                        for (let [key, value] of Object.entries(error.responseJSON.errors)) {
                            errorHTL += '<li>' + value + '</li>';
                        }

                        errorHTL += '</ul>';
                    }

                    toastr.error(errorHTL, 'Transação não executada:');

                    $(".showLoading").removeClass('loading');
                });
        }

        // Passo 3 - Salvar o token da transação enviado pelo pag seguro
        function finishDonation(transactionToken) {

            $('#step_payment').removeClass('active');
            $('#step_confirmation').removeClass('disabled').addClass('active');

            let campaign_url = '{{ route('donation.store') }}';
            let campaign_data = {
                campaign_id: $('#campaignId').val(),
                donated_amount: $('#donationAmount').val(),
                transaction_token: transactionToken,
                '_token' : $('input[name=_token]').val()
            };

            $.post(campaign_url, campaign_data).then(
                function (campaign_result) {
                    $('#steps').addClass('hidden');
                    window.location = '{{ route('checkout_thanks', ['id' => 0]) }}';
                },
                function (camp_error) {
                    $(".showLoading").removeClass('loading');
                    window.location = '{{ route('checkout_thanks', ['id' => 0]) }}';
                }
            );
        }

    </script>
@endsection

