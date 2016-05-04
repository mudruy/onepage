<?php
/**
 * interface of nvChecker classes
 *
 * @author pavel
 */
interface Ap_Application_Checker
{
  /**
   *  проверяет можно ли таске продолжать работу
   * @return boolean
   */
  public function hasLock();
}