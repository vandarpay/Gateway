<?php

namespace Vandar\Gateway\Saman;

use Illuminate\Support\Facades\Input;
use SoapClient;
use Vandar\Gateway\PortAbstract;
use Vandar\Gateway\PortInterface;

class Saman extends PortAbstract implements PortInterface
{
  /**
   * Address of main SOAP server
   *
   * @var string
   */
  protected $serverUrl = 'https://sep.shaparak.ir/payment.aspx';

  /**
   * {@inheritdoc}
   */
  public function set($amount)
  {
    $this->amount = $amount;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function ready()
  {
    $this->newTransaction();

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function redirect()
  {
    $data = [
      'amount' => $this->amount,
      'merchant' => $this->getMerchant(),
      'cellNumber' => $this->getCellNumber(),
      'resNum' => $this->transactionId(),
      'callBackUrl' => $this->getCallback()
    ];

    // dd($data);
    return \View::make('gateway::saman-redirector')->with($data);
  }

  /**
   * {@inheritdoc}
   */
  public function verify($transaction)
  {
    parent::verify($transaction);

    if ($transaction->merchant_id != null && $transaction->merchant_password != null) {
      $this->setMerchant($transaction->merchant_id);
      $this->setMerchantPassword($transaction->merchant_password);
    }

    $this->userPayment();
    $this->verifyPayment();

    return $this;
  }

  /**
   * Sets callback url
   * @param $url
   */
  function setCallback($url)
  {
    $this->callbackUrl = $url;
    return $this;
  }

  function setCellNumber($cellNumner)
  {
    $this->cellNumber = $cellNumner;
    return $this;
  }

  function getCellNumber()
  {
    return $this->cellNumber;
  }

  function setMerchant($merchant)
  {
    $this->merchant = $merchant;
    return $this;
  }

  function getMerchant()
  {
    if (!$this->merchant)
      $this->merchant = $this->config->get('gateway.saman.merchant');

    return $this->merchant;
  }

  function setMerchantPassword($password)
  {
    $this->merchant_password = $password;
    return $this;
  }

  function getMerchantPassword()
  {
    if (!$this->merchant_password)
      $this->merchant_password = $this->config->get('gateway.saman.password');

    return $this->merchant_password;
  }

  /**
   * Gets callback url
   * @return string
   */
  function getCallback()
  {
    if (!$this->callbackUrl)
      $this->callbackUrl = $this->config->get('gateway.saman.callback-url');

    $url = $this->makeCallback($this->callbackUrl, ['transaction_id' => $this->transactionId()]);

    return $url;
  }


  /**
   * Check user payment
   *
   * @return bool
   *
   * @throws SamanException
   */
  protected function userPayment()
  {
    $this->refId = Input::get('RefNum');
    $this->trackingCode = Input::get('TRACENO');
    $this->cardNumber = Input::get('SecurePan');
    $payRequestRes = Input::get('State');
    $payRequestResCode = Input::get('StateCode');

    if ($payRequestRes == 'OK') {
      return true;
    }

    $this->transactionFailed();
    $this->newLog($payRequestResCode, @SamanException::$errors[$payRequestRes]);
    throw new SamanException($payRequestRes);
  }


  /**
   * Verify user payment from bank server
   *
   * @return bool
   *
   * @throws SamanException
   * @throws SoapFault
   */
  protected function verifyPayment()
  {

    $fields = array(
      "merchantID" => $this->getMerchant(),
      "RefNum" => $this->refId,
      "password" => $this->getMerchantPassword(),
    );


    try {
      $soap = new SoapClient($this->serverUrl);
      $response = $soap->VerifyTransaction($fields["RefNum"], $fields["merchantID"]);
    } catch (\SoapFault $e) {
      $this->transactionFailed();
      $this->newLog('SoapFault', $e->getMessage());
      throw $e;
    }

    $response = intval($response);

    if ($response == $this->amount) {
      $this->transactionSucceed();
      return true;
    }

    //Reverse Transaction
    if ($response > 0) {
      try {
        $soap = new SoapClient($this->serverUrl);
        $response = $soap->ReverseTransaction($fields["RefNum"], $fields["merchantID"], $fields["password"], $response);
      } catch (\SoapFault $e) {
        $this->transactionFailed();
        $this->newLog('SoapFault', $e->getMessage());
        throw $e;
      }
    }

    //
    $this->transactionFailed();
    $this->newLog($response, SamanException::$errors[$response]);
    throw new SamanException($response);
  }
}
