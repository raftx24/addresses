<?php

namespace LaravelEnso\AddressesManager\app\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LaravelEnso\AddressesManager\app\Enums\StreetTypes;
use LaravelEnso\AddressesManager\App\Http\Requests\ValidateAddressRequest;
use LaravelEnso\AddressesManager\app\Models\Address;
use LaravelEnso\Core\app\Exceptions\EnsoException;
use LaravelEnso\FormBuilder\app\Classes\FormBuilder;

class AddressesController extends Controller
{

    public function index()
    {

        return view('laravel-enso/addressesmanager::index');
    }

    public function store(ValidateAddressRequest $request, string $type, int $id)
    {

        $address = new Address($request->all());
        $address->addressable_id = $id;
        $address->addressable_type = config('addresses.addressables.' . $type);;
        $address->save();

        return [
            'message'  => __('Created Address'),
            'redirect' => '',
        ];
    }

    public function update(ValidateAddressRequest $request, Address $address)
    {

        $address->fill($request->all());
        $address->save();

        return [
            'message' => __("The Changes have been saved!"),
        ];
    }

    /**
     * @param Address $address
     *
     * @return array
     * @throws \Exception
     */
    public function destroy(Address $address)
    {

        $address->delete();

        return [
            'message'  => __('Operation was successful'),
            'redirect' => '',
        ];
    }

    public function getEditForm(Address $address)
    {

        $editForm = (new FormBuilder($this->getFormPath(), $address))
            ->setTitle('Edit')
            ->setAction('PATCH')
            ->setUrl('/addresses/' . $address->id)
            ->setSelectOptions('street_type', (object) (new StreetTypes())->getData())
            ->getData();

        return $editForm;
    }

    public function getCreateForm(Request $request)
    {

        $postUrl = sprintf('/addresses/%s/%s',
            $request->get('addressable_type'), $request->get('addressable_id'));

        $createForm = (new FormBuilder($this->getFormPath()))
            ->setTitle('Insert')
            ->setAction('POST')
            ->setUrl($postUrl)
            ->setSelectOptions('street_type', (object) (new StreetTypes())->getData())
            ->getData();

        return $createForm;
    }

    /**
     * @return mixed
     * @throws EnsoException
     */
    public function list()
    {

        $addressable = $this->getAddressable();

        return $addressable->addresses()->get();
    }

    /**
     * @return mixed
     * @throws EnsoException
     */
    private function getAddressable()
    {

        return $this->getAddressableClass()::find(request()->get('id'));
    }

    /**
     * @return \Illuminate\Config\Repository|mixed
     * @throws EnsoException
     */
    private function getAddressableClass()
    {

        $class = config('addresses.addressables.' . request()->get('type'));

        if (!$class) {
            throw new EnsoException(
                __('Current entity does not exist in contacts.php config file: ') . request()->get('type')
            );
        }

        return $class;
    }

    /**
     * @return string
     */
    private function getFormPath(): string
    {
        $publishedForm = app_path('Forms/vendor/addresses/address.json');

        if(file_exists($publishedForm)) {
            return $publishedForm;
        }

        return __DIR__.'/../../Forms/addresses/address.json';
    }
}
