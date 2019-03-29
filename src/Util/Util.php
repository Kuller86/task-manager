<?php
/**
 * @author Alexander Ivanitsa <alexander.ivanitsa@gmail.com>
 */

namespace App\Util;

use Symfony\Component\Form\FormInterface;

class Util
{
    /**
     * @param FormInterface $form
     * @param array $errors
     * @param int $max_level
     * @param string $prefix
     * @param int $level
     */
    static public function fillFormErrors (FormInterface $form, &$errors = array(), $max_level = 0, $prefix = '', $level = 0)
    {
        if ($max_level > 0 && $max_level == $level) return;

        $formName = $prefix . ($prefix == '' ? '' : '.') . $form->getName();

        $formErrors = $form->getErrors();

        if ($formErrors->count()) {
            if ($level === 0) {
                $message = sprintf('%s (value: "%s", type: "%s")', $formErrors->current()->getMessage(), $formErrors->current()->getOrigin()->getViewData(), gettype($formErrors->current()->getOrigin()->getViewData()));
                $extra = $form->getExtraData();
                if (!empty($extra)) {
                    $message .= json_encode($extra);
                }
                $errors['__general'][] = $message;
            } else {
                //$message = $formErrors->current()->getMessage();
                $message = sprintf('%s (value: "%s", type: "%s")', $formErrors->current()->getMessage(), $formErrors->current()->getOrigin()->getViewData(), gettype($formErrors->current()->getOrigin()->getViewData()));
                $extra = $form->getExtraData();
                if (!empty($extra)) {
                    $message .= json_encode($extra);
                }
                $errors[$formName][] = $message;
            }
        }

        foreach ($form->all() as $child) {
            self::fillFormErrors($child, $errors, $max_level, $formName, $level + 1);
        }
    }
}